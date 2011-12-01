<?php

class Jfeatures_module extends JBaseModule {

	function process() {
		global $current_user;
		$current_user = new CurrentUser();
		switch ($_POST['action']) {
			case 'run':
				$this->runTest();
				break;
			case 'pause':
				$this->pauseTest();
				break;
			case 'check':
				$this->checkTest();
				break;
			case 'delete':
				$this->deleteTest();
				break;
		}
	}

	function error($s = 'ошибка') {
		$this->data['success'] = 0;
		$this->data['error'] = $s;
		return;
	}

	function deleteTest() {
		global $current_user;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}

		if ($current_user->getRole() < User::ROLE_SITE_ADMIN) {
			$this->error('Must be admin');
			return;
		}

		$id = isset($_POST['id']) ? (int) $_POST['id'] : false;
		if (!$id) {
			$this->error('Illegal id');
			return;
		}

		$feature = Features::getInstance()->getByIdLoaded($id);
		/* @var $feature Feature */
		if ($feature->loaded) {
			$query = 'UPDATE `features` SET `deleted`=1 WHERE `id`=' . $id;
			Database::query($query);
			$this->data = array(
			    'id' => $id,
			    'success' => 1
			);
		} else {
			$this->error('no feature to delete');
		}
	}

	function runTest() {
		global $current_user;
		$this->data['success'] = 0;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}
		/* @var $current_user CurrentUser */
		if ($current_user->getRole() < User::ROLE_SITE_ADMIN) {
			$this->error('Must be admin');
			return;
		}

		$id = isset($_POST['id']) ? (int) $_POST['id'] : false;
		if (!$id) {
			$this->error('Illegal id');
			return;
		}

		$feature = Features::getInstance()->getByIdLoaded($id);
		/* @var $feature Feature */
		
		$feature->setStatus(Feature::STATUS_WAIT_FOR_RUN, 'RUN FROM WEB INTERFACE BY ' . $current_user->getNickName());
		usleep(10000);
		$feature = Features::getInstance()->getByIdLoaded($id);
		$feature->loaded = false;
		$this->data = array(
		    'id' => $id,
		    'status_description' => $feature->getStatusDescription(),
		    'last_run' => date('Y/m/d H:i', $feature->getLastRun()),
		    'last_message' => 'waiting for run',
		    'success' => 1
		);
	}
	
	function pauseTest() {
		global $current_user;
		$this->data['success'] = 0;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}
		/* @var $current_user CurrentUser */
		if ($current_user->getRole() < User::ROLE_SITE_ADMIN) {
			$this->error('Must be admin');
			return;
		}

		$id = isset($_POST['id']) ? (int) $_POST['id'] : false;
		if (!$id) {
			$this->error('Illegal id');
			return;
		}

		$feature = Features::getInstance()->getByIdLoaded($id);
		/* @var $feature Feature */
		
		$feature->setStatus(Feature::STATUS_PAUSED, 'PAUSED FROM WEB INTERFACE BY ' . $current_user->getNickName());
		usleep(10000);
		$feature = Features::getInstance()->getByIdLoaded($id);
		$feature->loaded = false;
		$this->data = array(
		    'id' => $id,
		    'status_description' => $feature->getStatusDescription(),
		    'last_run' => date('Y/m/d H:i', $feature->getLastRun()),
		    'last_message' => 'waiting for run',
		    'success' => 1
		);
	}

	function checkTest() {
		global $current_user;
		$this->data['success'] = 0;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}

		if ($current_user->getRole() < User::ROLE_SITE_ADMIN) {
			$this->error('Must be admin');
			return;
		}

		$id = isset($_POST['id']) ? (int) $_POST['id'] : false;
		if (!$id) {
			$this->error('Illegal id');
			return;
		}

		$feature = Features::getInstance()->getByIdLoaded($id);
		/* @var $feature Feature */
		$this->data = array(
		    'id' => $id,
		    'status_description' => $feature->getStatusDescription(),
		    'last_run' => date('Y/m/d H:i', $feature->getLastRun()),
		    'last_message' => $feature->getLastMessage(),
		    'success' => 1
		);
	}

}