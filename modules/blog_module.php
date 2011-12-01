<?php

class blog_module extends CommonModule {

	function setCollectionClass() {
		$this->Collection = Blogposts::getInstance();
	}

	function _process($action, $mode) {
		switch ($action) {
			case 'show':
				$this->showBlogPosts();
				break;
			case 'new':
				$this->blogNew();
				break;
			
		}
	}

	function showBlogPosts() {
		$user = new user($this->params['blog_id']);
		/* @var $user User */
		$where = '';
		$sortings = array(
		    'add_time' => array('title' => 'по дате добавления', 'order' => 'desc'),
		);
		$this->_list($where, array(), false, $sortings);
		$bids = array();

		$this->data['posts']['title'] = 'Блог пользователя ' . $user->getNickName();
		$this->data['posts']['count'] = $this->getCountBySQL($where);
	}
	
	function blogNew(){
		
	}

}