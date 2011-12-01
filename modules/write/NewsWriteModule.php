<?php

class NewsWriteModule extends BaseWriteModule {

	function write() {
		global $current_user;
		if (!$current_user->authorized)
			throw new Exception('Access Denied');

		$current_user->can_throw('edit_news');

	
		$newsItem = News::getInstance()->getByIdLoaded(Request::post('id'));
		if ($newsItem) {
			$this->_edit($newsItem);
		} else {
			$this->_new();
		}
	}

	function _edit($newsItem) {
		/* @var $newsItem NewsItem */
		$data = array(
		    'date' => Request::post('date'),
		    'update_time' => time(),
		    'image' => $image,
		    'title' => Request::post('title'),
		    'anons' => Request::post('anons'),
		    'html' => Request::post('html'),
		    'enabled' => 1
		);

		$data['image'] = $this->uploadImage();

		$newsItem->_update($data);
	}

	function uploadImage() {
		
	}

}
