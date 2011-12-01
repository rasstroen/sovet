<?php

class Blogpost extends BaseObjectClass {

	public $id;
	public $loaded = false;
	public $data;
	public $fieldsMap = array(
	    'id' => 'int',
	    'id_user' => 'int',
	    'date' => 'int',
	    'update_time' => 'int',
	    'image' => 'string',
	    'title' => 'string',
	    'anons' => 'html',
	    'html' => 'html',
	    'enabled' => 'string',
	    'comment_count' => 'int',
	);

	function __construct($id, $data = false) {
		$this->id = $id;
		if ($data) {
			if ($data == 'empty') {
				$this->loaded = true;
				$this->exists = false;
			}
			$this->load($data);
		}
	}

	function dropCache() {
		Blogposts::getInstance()->dropCache($this->id);
		$this->loaded = false;
	}

	function _create($data) {
		$tableName = Blogposts::getInstance()->tableName;
		$this->dropCache();
		return parent::_create($data, $tableName);
	}

	function _update($data) {
		$tableName = Blogposts::getInstance()->tableName;
		$this->dropCache();
		return parent::_update($data, $tableName);
	}

	function load($data = false) {
		if ($this->loaded)
			return false;
		if (!$data) {
			$query = 'SELECT * FROM `blogpost` WHERE `id`=' . $this->id;
			$this->data = Database::sql2row($query);
		}else
			$this->data = $data;
		$this->exists = true;
		$this->loaded = true;
	}

	function _show() {
		$data = $this->getListData();
		$data['html'] = $this->getHTML();
		return $data;
	}

	function getHTML() {
		$this->load();
		return $this->data['html'];
	}

	function getUrl($redirect = false) {
		$this->load();
		$id = $redirect ? $this->getDuplicateId() : $this->id;
		$user = Users::getById($this->data['user_id']);
		/* @var $user User */
		return Config::need('www_path') . '/blog/' . $user->data['nick'] . '/' . $id;
	}

	function getImage() {
		$this->load();
		return Config::need('www_path') . '/static/upload/blog/' . $this->data['image'];
	}

	function getCommentCount() {
		$this->load();
		return $this->data['comment_count'];
	}

	function getListData() {
		$user = Users::getById($this->data['user_id']);
		$out = array(
		    'id' => $this->id,
		    'title' => $this->getTitle(),
		    'anons' => $this->getAnons(),
		    'path' => $this->getUrl(),
		    'comment_count' => $this->getCommentCount(),
		    'image' => $this->getImage(),
		    'path' => Config::need('www_path') . '/blog/' . $user->data['nick'] . '/' . $this->id,
		    'path_edit' => Config::need('www_path') . '/blog/' . $user->data['nick'] . '/' . $this->id . '/edit',
		);
		return $out;
	}

	function getTitle() {
		$this->load();
		return $this->data['title'];
	}

	function getAnons() {
		$this->load();
		return $this->data['anons'];
	}

}
