<?php

class News extends Collection {

	public $className = 'Newsitem';
	public $tableName = 'news';
	public $itemName = 'newsitem';
	public $itemsName = 'news';

	public static function getInstance() {
		if (!self::$news_instance) {
			self::$news_instance = new News();
		}
		return self::$news_instance;
	}

	public function _create($data) {
		$item = new $this->className(0);
		$createdId = $item->_create($data);
		header('Location:' . Config::need('www_path') . '/news/' . $createdId);
		exit();
	}

}