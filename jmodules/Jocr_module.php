<?php

class Jocr_module extends JBaseModule {

	function process() {
		global $current_user;
		$current_user = new CurrentUser();
		switch ($_POST['action']) {
			case 'check':
				$this->check();
				break;
			case 'set':
				$this->set();
				break;
		}
	}

	function error($s = 'ошибка') {
		$this->data['success'] = 0;
		$this->data['error'] = $s;
		return;
	}

	function check() {
		global $current_user;
		$this->data['success'] = 1;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}

		$id_user = $current_user->id;
		$id_book = max(0, (int) $_POST['id_book']);

		$query = 'SELECT * FROM `ocr` WHERE `id_book`=' . $id_book . ' AND `id_user`=' . $id_user . '';
		$r = Database::sql2array($query);
		foreach ($r as $row) {
			$this->data['ocr'] = array(
			    'id_book' => $row['id_book'],
			    'status' => $row['status'],
			    'state' => $row['state'],
			);
		}
	}

	function set() {
		global $current_user;
		$this->data['success'] = 1;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}
	}

}