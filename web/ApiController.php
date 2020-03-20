<?php

class ApiController extends MController {
	protected $_action;
	
	public function run() {
		$actionname = MRoute::getAction($this->defaultAction);
		$actionname = strtolower($actionname);
		$first = substr($actionname,0,1);
		$main = substr($actionname,1);
		$actionname = 'action'.strtoupper($first).$main;
		$this->_action = $actionname;
		$this->$actionname();
	}
}

?>