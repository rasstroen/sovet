<?php

class banners_module extends CommonModule {

	function setCollectionClass() {
		$this->Collection = Banners::getInstance();
	}

	function _process($action, $mode) {
		switch ($action) {
			case 'show':
				switch ($mode) {
					case 'random':
						$this->_showRandom();
						break;
				}

				break;
			default:
				throw new Exception('no action #' . $action . ' banners_module');
				break;
		}
	}

	function _showRandom() {
		$this->_show(1);
	}

}