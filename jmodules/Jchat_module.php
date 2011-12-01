<?php

function sort_by_id($a, $b) {
	if ($a['id'] > $b['id'])
		return 1;
	if ($a['id'] < $b['id'])
		return -1;
	return 0;
}

class Jchat_module extends JBaseModule {

	public $max_messages = 100;
	public $onlineInterval = 30;

	function process() {
		global $current_user;
		$current_user = new CurrentUser();
		if ($current_user->authorized) {
			$this->setOnlineUser($current_user->id);
		}
		$this->data['success'] = 1;
		switch ($_POST['action']) {
			case 'authorize':
				$this->auth();
				break;
			case 'fetch':
				$this->fetch();
				break;
			case 'say':
				$this->say();
				break;
			default:
				$this->error('no action #' . $_POST['action']);
				break;
		}
	}

	function doAdminFunction($message) {
		$message = explode(' ', $message);
		switch ($message[0]) {
			case '/clear':
				Database::query('TRUNCATE `hard_chat`');
				// clearing chat
				$this->data['refresh'] = true;
				return true;
				break;
		}
	}

	function doUserFunction($message) {
		$message = explode(' ', $message);
		$push = true; // message will be in messages
		$private = 0; // is private?
		switch ($message[0]) {
			case '/to':
				$name = str_replace('/to', '', implode(' ',$message));
				$name = explode(':' , $name);
				$name = trim($name[0]);
				
				if ($name) {
					$private_user = new User($name);
					try {
						$private_user->load();
						$private = $private_user->id;
					} catch (Exception $e) {
						// no user, sorry
					}
					$message = str_replace('/to', '', implode(' ',$message));
					$message = str_replace($name.':', '', $message);
					$message = explode(' ', $message);
				}
				break;
		}
		$message = implode(' ', $message);

		// links
		$message = preg_replace('#(?<!\])http://[^\s\[<]+#i', "<a href=\"$0\" target=\"_blank\">$0</a>", $message);

		return array($push, $message, $private);
	}

	function error($s) {
		$this->data['error'] = $s;
		$this->data['success'] = 0;
	}

	function say() {
		global $current_user;
		/* @var $current_user CurrentUser */
		if (!$current_user->authorized) {
			$this->error('authorization required');
		}

		$message = trim(strip_tags($_POST['message']));
		if (!$message) {
			$this->error('illegal message');
		}

		list($push, $message, $private) = $message = $this->doUserFunction($message);

		// if admin executes a command
		if ($current_user->getRole() >= User::ROLE_SITE_ADMIN && $r = $this->doAdminFunction($message)) {
			$lid = max(0, (int) $_POST['last_message_received_id']);
		} else {
			if ($push) {
				$query = 'INSERT INTO `hard_chat` SET
                        `id_user`=' . $current_user->id . ',
			`is_private`=' . $private . ',
                        `message`=' . Database::escape($message) . ',
                        `time`=' . time();
				Database::query($query);
				$lid = Database::lastInsertId();
				$this->data = $this->getMessages($lid - 1, $current_user->id);
			}
		}
		$this->data['last_message_id'] = $lid;
		$this->data['success'] = 1;
		return;
	}

	function getMessages($from_id, $uid) {
		global $current_user;
		/* @var $current_user CurrentUser */
		$out = array();
		$messages = Database::sql2array('SELECT * FROM `hard_chat` ORDER BY `id` DESC LIMIT ' . $this->max_messages, 'id');
		foreach ($messages as &$m) {
			$m['date_time'] = date('Y/m/d H:i:s', $m['time']);
		}
		$uids = array();
		foreach ($messages as $id => $message) {
			// if id < requested last message
			if ($from_id && ($id <= $from_id))
				unset($messages[$id]);
		}
		$out['messages'] = array_slice($messages, -$this->max_messages);
		uasort($out['messages'], 'sort_by_id');
		foreach ($out['messages'] as $m) {
			if ($m['is_private'] == 0 || ($m['is_private'] == $current_user->id) || ($m['id_user'] == $current_user->id)) {
				$out['real_messages'][$m['time']] = $m;
				$uids[$m['id_user']] = $m['id_user'];
			}
		}
		if (isset($out['real_messages']))
			$out['messages'] = $out['real_messages'];
		else
			$out['messages'] = array();
		$out['users'] = $this->getMessagesUsers($uids);
		return $out;
	}

	function getMessagesUsers($uids) {
		$users = Users::getByIdsLoaded($uids);
		$out = array();
		foreach ($users as $user) {
			$out[$user->id] = $user->getListData();
		}
		return $out;
	}

	function fetch() {
		global $current_user;
		$last_received_id = max(0, (int) $_POST['last_message_received_id']);
		$last_received_time = max(0, (int) $_POST['last_message_received_time']);
		$this->data = $this->getMessages($last_received_id, $current_user->id);

		if (isset($_POST['get_onliners'])) {
			$get_onliners = max(0, (int) $_POST['get_onliners']);
			if ($get_onliners) {
				$this->data['online_users'] = $this->getChatOnlineUsers();
			}
		}
		$this->data['success'] = 1;
	}

	function setOnlineUser($uid) {
		$xcachename = 'uon_' . $uid;
		$uid_exists = Cache::get($xcachename);
		if (!$uid_exists) { // new user?
			$time = time();
			$query = 'INSERT INTO `hard_chat_online` SET `id`=' . $uid . ', `time`=' . $time . ' ON DUPLICATE KEY UPDATE `time`=' . $time;
			Database::query($query);
			Cache::set($xcachename, $uid, $this->onlineInterval);
			Cache::drop('chat_online_users');
		} else {
			
		}
	}

	function getChatOnlineUsers() {
		$xcachename = 'chat_online_users';
		$uids = Cache::get($xcachename);
		if ($uids !== null) {
			
		} else {
			$uids = array_keys(Database::sql2array('SELECT `id` FROM `hard_chat_online` WHERE `time`>' . (time() - $this->onlineInterval), 'id'));
			Cache::set($xcachename, $uids, $this->onlineInterval);
		}
		return $this->getMessagesUsers($uids);
	}

	function auth() {
		global $current_user;
		/* @var $current_user CurrentUser */
		if ($current_user->authorized) {
			$this->data['profile'] = $current_user->getListData();
		} else {
			$this->error('user is not authorized');
			return;
		}
	}

}