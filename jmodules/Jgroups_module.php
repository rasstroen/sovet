<?php

class Jgroups_module extends JBaseModule {

	function process() {
		global $current_user;
		$current_user = new CurrentUser();
		switch ($_POST['action']) {
			case 'delete':
				$this->deleteGroup();
				break;
		}
	}

	function error($s = 'ошибка') {
		$this->data['success'] = 0;
		$this->data['error'] = $s;
		return;
	}

	function deleteGroup() {
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

		$query = 'UPDATE `feature_groups` SET `deleted`=1 WHERE `id`=' . $id;
		Database::query($query);
		$query = 'UPDATE `features` SET `group_id`=0 WHERE `group_id`=' . $id;
		Database::query($query);
		$this->data['success'] = true;
	}

}