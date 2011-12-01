<?php

class Banners extends Collection {

	public $className = 'Banner';
	public $tableName = 'banners';
	public $itemName = 'banner';
	public $itemsName = 'banners';

	public static function getInstance() {
		if (!self::$banners_instance) {
			self::$banners_instance = new Banners();
		}
		return self::$banners_instance;
	}

	public function _create($data) {
		$item = new $this->className(0);
		$createdId = $item->_create($data);
		header('Location:' . Config::need('www_path') . '/banners/' . $createdId);
		exit();
	}

}