<?php

class Releases extends Collection {

	public $className = 'Release';
	public $tableName = 'releases';
	public $itemName = 'release';
	public $itemsName = 'releases';

	public static function getInstance() {
		if (!self::$releases_instance) {
			self::$releases_instance = new Releases();
		}
		return self::$releases_instance;
	}

	public function _create($data) {
		$item = new $this->className(0);
		$createdId = $item->_create($data);
		header('Location:' . Config::need('www_path') . '/releases/' . $createdId);
		exit();
	}

}