<?php

/**
 * 基础组件，任何东西都是组件
 * @author Administrator
 *
 */
class MComponent {
	protected $_data = array();
	
	public function __set($name, $value) {
		$this->_data [$name] = $value;
	}
	public function __get($name) {
		if (isset ( $this->_data [$name] )) {
			return $this->_data [$name];
		} else {
			return null;
		}
	}

	public function _initData($data){
	    if($data){
	        foreach ($data as $key=>&$value){
	            $this->_data[$key] = $value;
            }
        }
    }

    public function getData(){
	    return $this->_data;
    }

	public function __isset($name) {
		return isset ( $this->_data [$name] );
	}
	public function __unset($name) {
		unset ( $this->_data [$name] );
	}
	public function __call($name,$params){
		throw new MException("{class}没有方法{name}", array('{class}'=>get_class($this),'{name}'=>$name));
	}
}