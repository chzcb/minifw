<?php

abstract class MApplication extends MComponent{
	
	public $charset = 'utf-8';
	
	private $_components = array();

	public $type;

	public $config;
	
	protected function init(){
		$this->initComponents();
		$this->loadComponents();
	}
	
	protected function initComponents(){
	    
	}
	
	protected function loadComponents(){
		foreach($this->_components as $name=>$component){
			if(isset($component['class'])){
				Fw::addInclude($name, FW_PATH.$component['class']);
			}
		}
	}
	
	public function setComponent($name,$component){
		$this->_components[$name] = array(
			'class'=>$component
		);
	}
	
	public function getComponent($name){
		return $this->_components[$name];
	}

	public function run(){
		
	}

	abstract  public function setConfig($config);
}

?>