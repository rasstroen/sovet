<?php

class comments_module extends BaseModule {

	private $lastid = 0;

	function generateData() {
		switch ($this->action) {
			case 'list':
				$this->_list();
				break;
		}
	}

	function _list() {
		$doc_id = max(0, (int) $this->params['doc_id']);
		$table = $this->params['table'];
		$query = 'SELECT * FROM `comments` WHERE `table`=' . Database::escape($table) . ' AND `doc_id`=' . $doc_id;
		$comments = Database::sql2array($query);

		$user_ids = array();
		$parents = array();

		$commentsNode = array();

		foreach ($comments as &$comment) {
			$user_ids[$comment['id_author']] = $comment['id_author'];
			$comment['date'] = date('Y/m/d H:i:s', $comment['time']);
			$parents[$comment['parent']][$comment['id']] = $comment;
		}
		$commentsNode = array();
		$this->addCommentsLevel($comments, 0, &$commentsNode, $parents);
		$this->data['comments'] = $commentsNode;

		$this->data['users'] = $this->getCommentUsers($user_ids);
		$this->data['comments']['doc_id'] = $doc_id;
		$this->data['comments']['table'] = $table;
	}

	function addCommentsLevel($comments, $parent_id, &$commentsNode, $parents) {
		if (isset($parents[$parent_id]))
			foreach ($parents[$parent_id] as $parent) {
				$this->lastid++;
				$commentsNode[$parent['id']] = $parent;
				$commentsNode[$parent['id']]['mod'] = (int) ($this->lastid % 2);
				$this->addCommentsLevel($comments, $parent['id'], $commentsNode[$parent['id']], $parents);
			}
		return $commentsNode;
	}

	function getCommentUsers($ids) {
		$users = Users::getByIdsLoaded($ids);
		$out = array();
		/* @var $user User */
		if (is_array($users))
			foreach ($users as $user) {
				$out[] = $user->getListData();
			}
		return $out;
	}

}