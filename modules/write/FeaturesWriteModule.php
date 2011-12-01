<?php

class FeaturesWriteModule extends BaseWriteModule {

	function process() {
		switch (Request::post('action')) {
			case 'run':
				$this->_run();
				break;
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
		    'description' => isset(Request::$post['description']) ? prepare_review(Request::$post['description']) : false,
		    'filepath' => isset(Request::$post['filepath']) ? prepare_review(Request::$post['filepath'], '') : false,
		    'group_id' => isset(Request::$post['group_id']) ? (int) Request::$post['group_id'] : false,
		    'last_run' => 0,
		    'status' => 0,
		    'last_message' => '',
		);
		if ($data['title'])
			Features::getInstance()->_create($data);
		@ob_end_clean();
		header('Location: ' . Config::need('www_path') . '/features');
		exit(0);
	}

	function _update() {
		$data = array(
		    'id' => isset(Request::$post['id']) ? prepare_review(Request::$post['id'], '') : false,
		    'title' => isset(Request::$post['title']) ? prepare_review(Request::$post['title'], '') : false,
		    'description' => isset(Request::$post['description']) ? prepare_review(Request::$post['description']) : false,
		    'filepath' => isset(Request::$post['filepath']) ? prepare_review(Request::$post['filepath'], '') : false,
		    'group_id' => isset(Request::$post['group_id']) ? (int) Request::$post['group_id'] : false,
		    'db_modify' => time(),
		);
		if ($data['title'] && $data['id'])
			Features::getInstance()->getByIdLoaded($data['id'])->_update($data);
		
		if ($data['description']) {
			// пишем в файл
			$f = '../features/' . Features::getInstance()->getByIdLoaded($data['id'])->getFilePath();
			if (!file_exists($f)) {
				@mkdir('../features/' . Features::getInstance()->getByIdLoaded($data['id'])->getFolder());
				file_put_contents($f, $data['description']);
				$file_modify = @filemtime($f);
				clearstatcache(); 
				$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
				Database::query($query);
			} else {
				$file_modify = @filemtime($f);
				if ($file_modify > Request::post('file_modify')) {
					// файл новее чем в базе 
					$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
					Database::query($query);
					throw new Exception(date('Y-m-d H:i:s').' File was modified at ' . date('Y-m-d H:i:s', $file_modify) . ', fetched version is ' . date('Y-m-d H:i:s', Request::post('file_modify')) . '. Please refresh page');
				} else {
					file_put_contents($f, $data['description']);
					clearstatcache(); 
					$file_modify = @filemtime($f);
					clearstatcache(); 
					$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
					Database::query($query);
				}
			}
		}

		@ob_end_clean();
		header('Location: ' . Config::need('www_path') . '/features');
		exit(0);
	}

	function _run() {
		$id = Request::post('id');
		if ($id) {
			$feature = Features::getInstance()->getByIdLoaded($id);
			list($success, $description) = $feature->_run();
			$this->setWriteParameter('features_module', 'run_result', implode("\n", $description));
		}
	}

}