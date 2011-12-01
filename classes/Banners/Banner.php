<?php

class Banner extends BaseObjectClass {

	public $id;
	public $loaded = false;
	public $data;
	public $fieldsMap = array(
	    'id' => 'int',
	    'width' => 'int',
	    'height' => 'int',
	    'file' => 'string',
	    'link' => 'string',
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
		Banners::getInstance()->dropCache($this->id);
		$this->loaded = false;
	}

	function _create($data) {
		$tableName = Banners::getInstance()->tableName;
		$this->dropCache();
		return parent::_create($data, $tableName);
	}

	function _update($data) {
		$tableName = Banners::getInstance()->tableName;
		$this->dropCache();
		return parent::_update($data, $tableName);
	}

	function load($data = false) {
		if ($this->loaded)
			return false;
		if (!$data) {
			$query = 'SELECT * FROM `Banners` WHERE `id`=' . $this->id;
			$this->data = Database::sql2row($query);
		}else
			$this->data = $data;
		$this->exists = true;
		$this->loaded = true;
	}

	function _show() {
		$data = $this->getListData();
		return $data;
	}


	function getListData() {
		$this->load();
		$out = array(
		    'id' => $this->id,
		    'width' => $this->data['width'],
		    'height' => $this->data['height'],
		    'file' => $this->data['file'],
		    'link' => $this->data['link'],
		);
		return $out;
	}
}
