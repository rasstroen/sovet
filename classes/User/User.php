<?php

// класс, отвечающий за юзера
class User {
	const ROLE_ANON = 0; // аноним
	const ROLE_READER_UNCONFIRMED = 10; // юзер с неподтвержденным мылом
	const ROLE_VANDAL = 20; // вандал
	const ROLE_READER_CONFIRMED = 30; // юзер с подтвержденным мылом
	const ROLE_MODERATOR = 40; // бибер
	const ROLE_SITE_ADMIN = 50; // админ вся руси

	public $id = 0;
	// users
	public $changed = array();
	public $profile = array();
	public $shelfLoaded = false;
	public $shelf;
	public $loaded = false;
	//users_additional
	public $changedAdditional = array(); // mongodb stored
	public $loadedAdditional; // if mongodb document fetched
	//
	public $profile_xml = array();
	public $xml_fields = array(
	    'id',
	    'nick',
	    'lastSave',
	    'lastLogin',
	);
	public $lovedLoaded = false;
	public $loved;

	function __construct($id = false, $data = false) {
		$this->loaded = false;
		if ($id && !is_numeric($id)) {
			$query = 'SELECT `id` FROM `users` WHERE `nick`=' . Database::escape($id);
			$id = (int) Database::sql2single($query);
		}
		if ($id) {
			$this->id = max(0, $id);
		}
		if ($data)
			$this->load($data);
	}

	function can($action, $target_user = false) {
		return AccessRules::can($this, $action, $target_user);
	}
	
	function can_throw($action, $target_user = false) {
		return AccessRules::can($this, $action, $target_user, $throwError = true);
	}

	function checkRights($right_name) {
		switch ($right_name) {
			// todo for check rights
		}
	}

	function loadLoved() {
		if ($this->lovedLoaded)
			return true;
		$this->loved = array();
		$this->lovedLoaded = true;
		$query = 'SELECT * FROM `users_loved` WHERE `id_user`=' . $this->id;
		$res = Database::sql2array($query);
		foreach ($res as $row) {
			$this->loved[$row['target_type']][$row['id_target']] = $row['id_target'];
		}
	}

	function getLoved($type) {
		if (!$this->lovedLoaded) {
			$this->loadLoved();
		}
		return isset($this->loved[(int) $type]) ? $this->loved[(int) $type] : array();
	}

	function getListData() {
		return array(
		    'id' => $this->id,
		    'picture' => $this->getAvatar(),
		    'nick' => $this->getNickName(),
		    'nickname' => $this->getNickName(),
		    'lastSave' => $this->profile['lastSave'],
		    'path' => $this->getUrl(),
		    'role' => $this->getRole(),
		);
	}

	function getUrl() {
		return Config::need('www_path') . '/user/' . $this->id;
	}

	function checkInBookshelf($_id_book) {
		$shelf = $this->getBookShelf();
		foreach ($shelf as $shelf_id => $data) {
			foreach ($data as $id_book => $data) {
				if ($id_book == $_id_book)
					return $shelf_id;
			}
		}
		return false;
	}

	function getBookShelf() {
		if ($this->shelfLoaded)
			return $this->shelf;
		$query = 'SELECT * FROM `users_bookshelf` WHERE `id_user`=' . $this->id;
		$array = Database::sql2array($query);
		$out = array();
		foreach ($array as $row) {
			$out[$row['bookshelf_type']][$row['id_book']] = $row;
		}
		$this->shelfLoaded = true;
		$this->shelf = $out;
		return $this->shelf;
	}

	function AddBookShelf($id_book, $id_shelf) {
		$id_book = max(0, (int) $id_book);
		$id_shelf = max(0, (int) $id_shelf);
		$time = time();
		$query = 'INSERT INTO `users_bookshelf` SET `id_user`=' . $this->id . ',`id_book`=' . $id_book . ', `bookshelf_type`=' . $id_shelf . ', `add_time`=' . $time . '
			ON DUPLICATE KEY UPDATE `id_book`=' . $id_book . ', `bookshelf_type`=' . $id_shelf . ', `add_time`=' . $time . '';
		Database::query($query);
		$this->shelf[$id_shelf][$id_book] = array(
		    'id_user' => $this->id,
		    'id_book' => $id_book,
		    'bookshelf_type' => $id_shelf,
		    'add_time' => $time
		);
		$event = new Event();
		$event->event_addShelf($this->id, $id_book, $id_shelf);
		$event->push();
	}

	// кто меня читает
	function setFollowers(array $array) {
		$this->loadAdditional();
		$this->changedAdditional['followers'] = $this->profileAdditional['followers'] = $array;
	}

	// кого я читаю
	function setFollowing(array $array) {
		$this->loadAdditional();
		$this->changedAdditional['following'] = $this->profileAdditional['following'] = $array;
	}

	// вернуть тех, кого я читаю
	function getFollowing() {
		$this->loadAdditional();
		return isset($this->profileAdditional['following']) ? $this->profileAdditional['following'] : array();
	}

	// вернуть всех, кто меня читает
	function getFollowers() {
		$this->loadAdditional();
		return isset($this->profileAdditional['followers']) ? $this->profileAdditional['followers'] : array();
	}

	// когда юзера зафрендили
	function onNewFollower() {
		
	}

	// когда юзер зафрендил кого-либо
	function onNewFollowing() {
		
	}

	public function getTheme() {
		return Config::need('default_theme');
	}

	public function getNickName() {
		$this->load();
		if ($this->getProperty('nick'))
			return $this->getProperty('nick');
		$email = $this->getProperty('email');
		return substr($email, 1, strpos($email, '@'));
	}

	public function getAvatar() {
		$this->load();
		$pic = $this->getProperty('avatar') ? ($this->id . '.' . $this->getProperty('avatar')) : 'default.jpg';
		return Config::need('www_path') . '/static/upload/avatars/' . $pic;
	}

	public function getLanguage() {
		return Config::need('default_language');
	}

	function register($nickname, $email, $password) {
		$hash = md5($email . $nickname . $password . time());
		$query = 'INSERT INTO `users` SET
			`email`=\'' . $email . '\',
			`pass`=\'' . md5($password) . '\',
			`nick`=\'' . $nickname . '\',
			`role`=\'' . User::ROLE_READER_CONFIRMED . '\',
			`hash` = \'' . $hash . '\'';
		if (Database::query($query)) {
			$this->id = Database::lastInsertId();
			if ($this->id) {
				return $hash;
			}
		}
		return false;
	}

	// отправляем в xml информацию о пользователе
	public function setXMLAttibute($field, $value) {
		if (in_array($field, $this->xml_fields))
			$this->profile_xml[$field] = $value;
	}

	// отдаем информацию по пользователю для отображения в xml
	public function getXMLInfo() {
		$this->load();
		$out = $this->profile_xml;
		$out['nickname'] = $out['nick'];
		return $out;
	}

	// грузим дополнительню информацию
	public function loadAdditional($rowData = false) {
		if ($this->loadedAdditional)
			return true;
		$this->loadedAdditional = true;
		$this->profileAdditional = array();
		return;
	}

	// грузим информацию по пользователю
	public function load($rowData = false) {
		if ($this->loaded)
			return true;
		if (!$rowData) {
			if (!$this->id) {
				$this->setXMLAttibute('auth', 0);
			} else {
				if ($cachedUser = Users::getFromCache($this->id)) {
					$this->profile = $cachedUser->profile;
					foreach ($this->profile as $field => $value) {
						$this->setXMLAttibute($field, $value);
					}
					$this->loaded = true;
					return;
				} else {
					$rowData = Database::sql2row('SELECT * FROM `users` WHERE `id`=' . $this->id);
				}
			}
		}
		if (!$rowData) {
			// нет юзера в базе
			throw new Exception('Такого пользователя не существует', Error::E_USER_NOT_FOUND);
		}

		$this->id = (int) $rowData['id'];

		foreach ($rowData as $field => $value) {
			if ($field == 'serialized') {
				$arr = json_decode($value, true);
				if (is_array($arr))
					foreach ($arr as $field => $value) {
						$this->setPropertySerialized($field, $value, $save = false);
						$this->setXMLAttibute($field, $value);
					}
			}
			// все данные в profile
			$this->setProperty($field, $value, $save = false);
			// данные для xml - в xml
			$this->setXMLAttibute($field, $value);
		}
		Users::add($this);
		$this->loaded = true;
		Users::putInCache($this->id);
		return;
	}

	public function setRole($role) {
		$this->setProperty('role', $role);
		$this->setProperty('hash', '');
	}

	public function getRole() {
		return (int) $this->getProperty('role');
	}

	public function getBdayString($default = 'неизвестно') {
		if ($this->getProperty('bday')) {
			
		} else {
			return $default;
		}
	}

	public function getBday($default = 0, $format = 'Y-m-d') {
		return date($format, (int) $this->getProperty('bday', $default));
	}

	public function getRoleName($id = false) {
		if (!$id)
			$id = $this->getRole();
		return isset(Users::$rolenames[$id]) ? Users::$rolenames[$id] : User::ROLE_READER_UNCONFIRMED;
	}

	public function setPropertySerialized($field, $value, $save = true) {
		$this->loadAdditional();
		if (!$save)
			$this->profileAdditional[$field] = $value;
		else
			$this->profileAdditional[$field] = $this->changedAdditional[$field] = $value;
	}

	public function setProperty($field, $value, $save = true) {
		if (!$save)
			$this->profile[$field] = $value;
		else
			$this->profile[$field] = $this->changed[$field] = $value;
	}

	public function getProperty($field, $default = false) {
		$this->load();
		return isset($this->profile[$field]) ? $this->profile[$field] : $default;
	}

	public function getPropertySerialized($field, $default = false) {
		$this->loadAdditional();
		return isset($this->profileAdditional[$field]) ? $this->profileAdditional[$field] : $default;
	}

	function __destruct() {
		
	}

	function save() {
		// основные поля
		if (count($this->changed) && $this->id) {
			$this->changed['lastSave'] = time();
			foreach ($this->changed as $f => $v)
				$sqlparts[] = '`' . $f . '`=\'' . mysql_escape_string($v) . '\'';
			$sqlparts = implode(',', $sqlparts);
			$query = 'INSERT INTO `users` SET `id`=' . $this->id . ',' . $sqlparts . ' ON DUPLICATE KEY UPDATE ' . $sqlparts;

			Database::query($query);
		}
		Users::dropCache($this->id);
	}

}