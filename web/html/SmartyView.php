<?php
require_once FW_PATH.'/libs/smarty/Smarty.class.php';

abstract class SmartyView {
	public $_layoutPath;
	private $_layout;
	
	private $_model;
	
	public function __construct(){
		ob_start();
		$this->smarty = new Smarty();
		$this->smarty->compile_dir = './.smarty/compile_dir/';
		$this->smarty->cache_dir = './.smarty/cache_dir/';
        if(!file_exists($this->smarty->compile_dir)){
            mkdir($this->smarty->compile_dir,0777,true);
        }
        if(!file_exists($this->smarty->cache_dir)){
            mkdir($this->smarty->cache_dir,0777,true);
        }

        //设置默认变量
		$this->smarty->assign('webRoot',MRoute::getBaseUrl());
	}
	
	public function setLayout($layout){
		$this->_layout = $layout;
	}
	
	public function setModel($model){
		$this->_model = $model;
	}
	
	public function setData($data=array()){
		if($data != null){
			foreach($data as $key=>$value)
			{
				$this->smarty->assign($key,$value);
			}
		}
		
	}
	
	public function _display(){
		$layoutFile = $this->_layoutPath.'/'.$this->_layout.'.tpl';
		if(file_exists($layoutFile)){
			$this->smarty->display($this->_layoutPath.'/'.$this->_layout.'.tpl');
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