<?php

class CommentsWriteModule extends BaseWriteModule {

	function process() {
		global $current_user;
		/* @var $current_user CurrentUser */
		$current_user->can_throw('add_comments');
		/*
		  [writemodule] => CommentsWriteModule
		  [reply_to] => 1
		  [doc_id] => 440
		  [comment] => ghjkhjk
		 */
		$document_id = max(0, (int) Request::post('doc_id'));
		$table = Request::post('table');
		$comment = Request::post('comment');
		$reply_to = max(0, (int) Request::post('reply_to'));

		Database::query('START TRANSACTION');

		$query = 'SELECT max(`id`) as `id` FROM `comments` WHERE `doc_id` = ' . $document_id . ' AND `table`=' . Database::escape($table) . '';
		$maxid = 1 + max(0, Database::sql2single($query));

		$query = 'INSERT INTO `comments` SET 
		`id`=' . $maxid . ',
		`table`=' . Database::escape($table) . ', 
		`comment`=' . Database::escape($comment) . ',
		`parent`=' . $reply_to . ',
		`doc_id`=' . $document_id . ',
		`id_author`=' . $current_user->id . ',
		`time`=' . time();
		Database::query($query);


		Database::query('COMMIT');
	}

}
?>

<?php

class write_comment {

	function __construct() {
		$is_auth = rpc::$auth;
		if (!$is_auth)
			return;
		$uid = rpc::$user['uid'];
		if (!$uid)
			return;


		$text = rpc::$postparams['comment'];
		$text = trim(strip_tags($text));
		if (!$text)
			return;

		$doc_id = (int) rpc::$params['doc_id'];
		if (!$doc_id)
			return;


		$table = mysql_escape_string(rpc::$params['table']);
		$rtable = $table;
		if ($table == 'releases') {
			$table = 'news';
			$rtable = 'releases';
		}

		if ($table == 'video') {
			$table = 'news';
			$rtable = 'video';
		}

		if ($table == 'radio') {
			$table = 'news';
			$rtable = 'radio';
		}


		if (!$table)
			return;

		$query = 'SELECT max(`id`) as `id` FROM `comments` WHERE
            `doc_id` = ' . $doc_id . ' AND
            `table`=\'' . $table . '\'';
		$id = _database::torow($query);

		$id = isset($id['id']) ? $id['id'] + 1 : 1;

		$parent = rpc::$params['reply_to'];
		$query = 'INSERT INTO `comments` SET
            `doc_id`=' . $doc_id . ',
            `table`=\'' . $table . '\',
            `id`=' . $id . ',
            `parent`=' . $parent . ',
            `id_author`=' . $uid . ',
            `time`=' . time() . ',
            `comment`=\'' . mysql_escape_string($text) . '\'';
		_database::query($query);

		rpc::$params['write_' . $rtable]['redirect'] = $id;

		$query = 'UPDATE `module_' . $table . '` SET `comment_count`=
            (SELECT COUNT(1)  FROM `comments` WHERE
            `doc_id` = ' . $doc_id . ' AND
            `table`=\'' . $table . '\') WHERE id=' . $doc_id;
		_database::query($query);

		$query = 'UPDATE `users` SET comments = (SELECT COUNT(1)  FROM `comments` WHERE `id_author` = ' . $uid . ')
            WHERE `uid`=' . $uid;
		_database::query($query);
	}

}
?>
