<?php

// класс, отвечающий за текущего юзера
class CurrentUser extends User {

	public $new_messages_cachetime = 60;
	public $xml_fields = array(// в отличие от всех юзеров для текущего мы можем выдавать больше данных
	    'id',
	    'email',
	    'role',
	    'nick',
	    'lastSave',
	);
	public $authorized = false;
	public $hash = '';

	function __construct() {
		parent::__construct();
		$this->uid = $this->authorize_cookie();
	}

	public function getProperty($field, $default = false) {
		return isset($this->profile[$field]) ? $this->profile[$field] : $default;
	}

	public function getXMLInfo() {
		$this->load();
		$out = $this->profile_xml;
		$out['new_messages'] = $this->getNewMessagesCount();
		return $out;
	}

	public function getNewMessagesCount() {
		$cacheName = 'messages_count_' . $this->id;
		if (!isset($this->new_messages_count)) {
			if (($this->new_messages_count = Cache::get($cacheName)) === null) {
				$query = 'SELECT COUNT(1) FROM `users_messages_index` WHERE `id_recipient`=' . $this->id . ' AND `is_new`=1';
				$this->new_messages_count = Database::sql2single($query);
				Cache::set($cacheName, (int) $this->new_messages_count, $this->new_messages_cachetime);
			}
		}
		return (int) $this->new_messages_count;
	}

	public function logout() {
		$this->authorized = false;
		$this->id = 0;
		$this->setAuthCookie($value, true);
	}

	// именно залогинились
	public function onLogin() {
		$hash = md5($this->id . ' ' . time() . ' ' . rand(1, 1000));
		$query = 'INSERT INTO `users_session` SET
			`user_id`=' . $this->id . ',
			`session`=\'' . $hash . '\',
			`expires`=' . (time() + Config::need('auth_cookie_lifetime')) . '
			ON DUPLICATE KEY UPDATE
			`session`=\'' . $hash . '\',
			`expires`=' . (time() + Config::need('auth_cookie_lifetime'));
		Database::query($query);
		Cache::drop('auth_' . $this->id);
		$this->setProperty('lastLogin', time());
		$this->setAuthCookie($hash);
	}

	public function getAvailableNickname($nickname, $additional = '') {
		$nickname = trim($nickname) . $additional;
		$query = 'SELECT `nick` FROM `users` WHERE `nick` LIKE \'' . $nickname . '\' LIMIT 1';
		$row = Database::sql2single($query);
		if ($row && $row['nick']) {
			return $this->getAvailableNickname($nickname, $additional . rand(1, 99));
		}
		return $nickname;
	}

	private function setAuthCookie($value, $delete = false) {
		if ($delete) {
			$time = time() - 1;
		} else {
			$time = time() + Config::need('auth_cookie_lifetime');
		}
		Request::headerCookie(Config::need('auth_cookie_hash_name'), $value, $time, '/', Config::need('www_domain'), false, false);
		Request::headerCookie(Config::need('auth_cookie_id_name'), $this->id, time() + Config::need('auth_cookie_lifetime'), '/', Config::need('www_domain'), false, false);
	}

	// авторизуем пользователя по кукам
	public function authorize_cookie() {
		$auth_cookie_name = Config::need('auth_cookie_hash_name');
		$auth_uid_name = Config::need('auth_cookie_id_name');
		$_COOKIE[$auth_uid_name] = isset($_COOKIE[$auth_uid_name]) ? $_COOKIE[$auth_uid_name] : '';
		$xcache_cookie = 'auth_' . (int) $_COOKIE[$auth_uid_name];
		$to_cache = true;
		if (isset($_COOKIE[$auth_cookie_name]) && isset($_COOKIE[$auth_uid_name])) {
			if ($row = Cache::get($xcache_cookie)) {
				$row = unserialize($row);
				$to_cache = false;
			} else {
				$query = 'SELECT `session`,`expires` FROM `users_session` WHERE `user_id`=' . (int) $_COOKIE[$auth_uid_name];
				$row = Database::sql2row($query);
			}
			if ($row) {
				if ($row['session'] == $_COOKIE[$auth_cookie_name] && $row['expires'] > time()) {
					$this->id = (int) $_COOKIE[$auth_uid_name];
					$this->load();
					$this->authorized = true;
					if ($to_cache)
						Cache::set($xcache_cookie, serialize($row), Config::need('auth_cookie_lifetime'));
				}
			}
		}else
			return false;
	}

	// авторизуем пользователя по логину и паролю
	public function authorize_password($email, $password, $md5used = false) {
		$row = Database::sql2row('SELECT * FROM `users` WHERE 
			(`email`=\'' . $email . '\' OR 
			`nick`=\'' . $email . '\')');
		if (!$row) {
			// нет такого пользователя
			return 'user_missed';
		}

		$password_ = $md5used ? $password : md5($password);
		if ($row) {
			if ($password_ != $row['pass'] && ($password != $row['pass'])) {
				return 'user_password';
			}
		}
		$this->load($row);
		$this->authorized = true;
		$this->onLogin();
		return true;
	}

}