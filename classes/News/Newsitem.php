<?php

class Newsitem extends BaseObjectClass {

	public $id;
	public $loaded = false;
	public $data;
	public $fieldsMap = array(
	    'id' => 'int',
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
		News::getInstance()->dropCache($this->id);
		$this->loaded = false;
	}

	function _create($data) {
		$tableName = News::getInstance()->tableName;
		$this->dropCache();
		return parent::_create($data, $tableName);
	}

	function _update($data) {
		$tableName = News::getInstance()->tableName;
		$this->dropCache();
		return parent::_update($data, $tableName);
	}

	function load($data = false) {
		if ($this->loaded)
			return false;
		if (!$data) {
			$query = 'SELECT * FROM `news` WHERE `id`=' . $this->id;
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
		$id = $redirect ? $this->getDuplicateId() : $this->id;
		return Config::need('www_path') . '/news/' . $id;
	}

	function getImage() {
		$this->load();
		return Config::need('www_path') . '/static/upload/news/' . $this->data['image'];
	}

	function getCommentCount() {
		$this->load();
		return $this->data['comment_count'];
	}

	function getListData() {
		$out = array(
		    'id' => $this->id,
		    'title' => $this->getTitle(),
		    'anons' => $this->getAnons(),
		    'path' => $this->getUrl(),
		    'comment_count' => $this->getCommentCount(),
		    'image' => $this->getImage(),
		    'path' => Config::need('www_path').'/news/'.$this->id,
		    'path_edit' => Config::need('www_path').'/news/'.$this->id.'/edit',
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
