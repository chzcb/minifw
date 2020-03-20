<?php

/**
 * 基础视图
 * @author Administrator
 *
 */
abstract class MView extends MComponent{
	public $_layoutPath;
	private $_layout;
	
	private $_model;
	
	public function __construct(){
		ob_start();
	}
	
	public function setLayout($layout){
		$this->_layout = $layout;
	}
	
	public function setModel($model){
		$this->_model = $model;
	}
	
	public function setData($data=array()){
		foreach($data as $key=>$value)
		{
			if(!isset($this->$key))
				$this->$key = $value;
		}
	}
	
	public function _display(){
		$layoutFile = $this->_layoutPath.'/'.$this->_layout.'.tpl';
		if(file_exists($layoutFile)){
			include $this->_layoutPath.'/'.$this->_layout.'.tpl';
		}
		else
		{
			throw new MException("没有找到视图布局文件:".$this->_layout.'.tpl');
		}
	}
	
	public function render(){
		if($this->_model){
			$this->setData($this->_model->getData());
		}
		$this->_display();
		$this->_content = ob_get_contents();
		ob_end_clean();

		return $this->_content;


	}
	
	public function setLayoutPath($layoutpath){
		$this->_layoutPath = $layoutpath;
	}
	abstract public function init($controller);
}

?>