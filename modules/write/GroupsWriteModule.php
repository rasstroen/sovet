<?php

class GroupsWriteModule extends BaseWriteModule {

	function process() {
		switch (Request::post('action')) {
			default :
				$this->_new();
				break;
		}
	}

	function _new() {
		if (Request::post('id'))
			return $this->_update();
		$data = array(
		    'title' => isset(Request::$post['title']) ? prepare_review(Request::$post['title'], '') : false,
		    'folder' => isset(Request::$post['folder']) ? prepare_review(Request::$post['folder']) : false,
		);
		if ($data['title'] && $data['folder'])
			$this->_upsert($data);
		@ob_end_clean();
		header('Location: ' . Config::need('www_path') . '/features');
		exit(0);
	}

	function _update() {
		$data = array(
		    'id' => isset(Request::$post['id']) ? prepare_review(Request::$post['id'], '') : false,
		    'title' => isset(Request::$post['title']) ? prepare_review(Request::$post['title'], '') : false,
		    'folder' => isset(Request::$post['folder']) ? prepare_review(Request::$post['folder']) : false,
		);
		if ($data['title'] && $data['folder'] && $data['id'])
			$this->_upsert($data);
		@ob_end_clean();
		header('Location: ' . Config::need('www_path') . '/features');
		exit(0);
	}

	function _upsert($data) {
		$q = array();
		foreach ($data as $field => $value) {
			$q[] = '`' . $field . '`=' . Database::escape($value);
		}
		if (count($q)) {
			Database::query('INSERT INTO `feature_groups` SET ' . implode(',', $q) . ' ON DUPLICATE KEY UPDATE  ' . implode(',', $q));
		}
		@ob_end_clean();
		header('Location: ' . Config::need('www_path') . '/features');
		exit(0);
	}

}