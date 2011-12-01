<?php

class features_module extends CommonModule {

	function setCollectionClass() {
		$this->Collection = Features::getInstance();
	}

	function _process($action, $mode) {
		switch ($action) {
			case 'list':
				switch ($mode) {
					default:
						$this->getFeaturesList();
						break;
				}
				break;
			case 'show':
				switch ($mode) {
					default:
						$this->_show($this->params['feature_id']);
						break;
				}
				break;
			case 'edit':
				switch ($mode) {
					default:
						$this->_show($this->params['feature_id']);
						$this->_new();
						break;
				}
				break;
			case 'new':
				switch ($mode) {
					default:
						$this->_new();
						break;
				}
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function _new() {
		$this->data['groups'] = Database::sql2array('SELECT * FROM `feature_groups` WHERE `deleted`=0');
	}

	function getFeaturesList() {
		$where = '`deleted`=0';
		$sortings = array(
		    'group_id' => array('group_id' => 'по группе', 'order' => 'asc'),
		);
		$data = $this->_list($where, array(), 1, $sortings);
		foreach ($data['features'] as &$item) {
			$item['path_edit'] = 'features/' . $item['id'] . '/edit';
			$item['path_delete'] = 'features/' . $item['id'] . '/delete';
		}
		$this->data['groups'] = array();
		$this->data['groups'] = $this->getInGroup($data);

		$this->data['features']['title'] = 'Тесты';
		$this->data['features']['count'] = $this->getCountBySQL($where);
	}

	function getInGroup($data) {
		$groups = array();
		foreach ($data['features'] as $item) {
			$groups[$item['group_id']] = $item['group_id'];
		}
		$query = 'SELECT * FROM `feature_groups` WHERE `id` IN(' . implode(',', $groups) . ') AND `deleted`=0';
		$groups = Database::sql2array($query, 'id');
		$i = 0;
		foreach ($data['features'] as $feature) {
			$groups[$feature['group_id']]['features'][] = $feature;
			if ($feature['group_id']) {
				$groups[$feature['group_id']]['path_edit'] = 'groups/' . $feature['group_id'] . '/edit';
				$groups[$feature['group_id']]['path_delete'] = 'groups/' . $feature['group_id'] . '/delete';
			}
		}
		if (isset($groups[0]))
			$groups[0]['title'] = 'без группы';

		return $groups;
	}

}