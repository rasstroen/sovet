<pre>
	<?php
	/*
	 * Старая база -  в новую базу
	 */
	ini_set('memory_limit', '1024M');
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	set_time_limit(0);
	include 'config.php';
	if (file_exists('localconfig.php'))
		require_once 'localconfig.php';
	else
		$local_config = array();

	Config::init($local_config);
	include 'include.php';
// users
	Database::query('TRUNCATE `users`');
	$query = 'SELECT * FROM `fashist`.`users`';
	$users = Database::sql2array($query);
	//`uid`, `sex`, `name`, `nick`, `email`, `role`, `pass`, `hash`, `avatar`, `comments`, `last_enter`, `last_action`, `reg_date`, `icq`, `phone`, `url`, `hard_email`, `ya_email_id`
	//`id`, `sex`, `name`, `nick`, `email`, `role`, `pass`, `hash`, `avatar`, `comments`, `last_enter`, `last_action`, `reg_date`, `icq`, `phone`, `url`, `hard_email`, `ya_email_id`, `lastSave`, `lastLogin`, `bday`, `city_id`
	foreach ($users as $user) {

		$user['role'] = $user['role'] == 5 ? 50 : 30;
		$user['id'] = $user['uid'];

		if (!$user['nick'])
			$user['nick'] = substr($user['email'], 1, strpos($user['email'], '@'));
		
		$newNick2 = $user['nick'].'_';
		unset($user['uid']);
		$sqlp = array();
		foreach ($user as $f => $v) {
			$sqlp[] = '`' . $f . '`=' . Database::escape($v);
		}

		$query = 'INSERT INTO `users` SET
			' . implode(',', $sqlp) . ' ON DUPLICATE KEY UPDATE `nick`='.Database::escape($newNick2);
		Database::query($query);
	}
// news
	Database::query('TRUNCATE `news`');
	$query = 'SELECT * FROM `fashist`.`module_news` WHERE `type`=0';
	$news = Database::sql2array($query, 'id');
	foreach ($news as $id => $newsitem) {
		$object = News::getInstance()->getById($id);
		/* @var $object Newsitem */
		$data = $newsitem;
		$data['update_time'] = strtotime($data['update_time']);
		$object->_create($data);
	}

// releases
	Database::query('TRUNCATE `releases`');
	$query = 'SELECT * FROM `fashist`.`module_news` WHERE `type`=1';
	$news = Database::sql2array($query, 'id');
	foreach ($news as $id => $newsitem) {
		$object = Releases::getInstance()->getById($id);
		/* @var $object Release */
		$data = $newsitem;
		$data['update_time'] = strtotime($data['update_time']);
		$object->_create($data);
	}

//comments
	Database::query('TRUNCATE `comments`');
	Database::query('ALTER TABLE `comments` DROP PRIMARY KEY', false);
	Database::query('INSERT INTO `comments` (SELECT * FROM `fashist`.`comments`)');
	// all releases comments comes to releases comments!
	$query = 'UPDATE  `comments` SET `table`=\'releases\' WHERE `table`=\'news\' AND `doc_id` IN (SELECT `id` FROM `releases`)';
	Database::query($query);
	
	Database::query('ALTER TABLE `hardtechno`.`comments` ADD PRIMARY KEY ( `doc_id` , `table` , `id` ) ');
	
// upload
	exec('rm -rf  /home/test.hardtechno.ru/static/upload/news/*', $o);
	print_r($o);
	exec('rm -rf  /home/test.hardtechno.ru/static/upload/avatars/*', $o);
	print_r($o);
	exec('cp  -r /home/hardtechno.ru/upload/news/* /home/test.hardtechno.ru/static/upload/news', $o);
	print_r($o);
	exec('cp  -r /home/hardtechno.ru/upload/avatar/* /home/test.hardtechno.ru/static/upload/avatars', $o);
	print_r($o);
	exec('chmod 777 -R /home/test.hardtechno.ru/static', $o);
	print_r($o);
	die('all');








	