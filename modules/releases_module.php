<?php

class releases_module extends CommonModule {

	function setCollectionClass() {
		$this->Collection = Releases::getInstance();
	}

	function _process($action, $mode) {
		switch ($action) {
			case 'list':
				switch ($mode) {
					case 'columns':
						$this->_listDefault();
						break;
					default:
						$this->_listDefault();
						break;
				}
				break;
			case 'new':
				$this->_new();
				break;

			case 'edit':case 'show':
				$this->_show($this->params['release_id']);
				break;
			default:
				throw new Exception('no action #' . $action . ' news_module');
				break;
		}
	}

	function _listDefault() {
		$where = '`enabled`=1';
		$sortings = array(
		    'date' => array('date' => 'по дате добавления', 'order' => 'desc'),
		);
		$data = $this->_list($where, array(), false, $sortings);
		if (isset($this->data['news'])) {
			foreach ($this->data['news'] as &$item) {
				$item['path_edit'] = 'releases/' . $item['id'] . '/edit';
				$item['path_delete'] = 'releases/' . $item['id'] . '/delete';
			}
		}
		$this->data['news']['title'] = 'Релизы';
		$this->data['news']['count'] = $this->getCountBySQL($where);
	}

}