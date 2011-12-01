<?php

class groups_module extends BaseModule {

	function generateData() {
		switch ($this->action) {
			case 'edit':
				$this->_edit();
				break;
			case 'new':
				$this->_new();
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function _edit() {
		$id = max(0, (int) (isset($this->params['group_id']) ? $this->params['group_id'] : false));
		if (!$id)
			throw new Exception('no group id');
		
		$query = 'SELECT * FROM `feature_groups` WHERE `id`=' . $id;
		$data = Database::sql2row($query);
		$this->data['group'] = $data;
	}
	
	function _new() {
		$this->data['group'] = array();
	}

}