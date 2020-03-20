<?php

class JSONController extends MController
{
	public $defaultAction = 'default';

	public $_action;

	public $name = '';

	public function run(){
		Fw::app()->type = 'JSON';
        //如果没有Action
        if(!($actionname = MRoute::getAction())){
            //如果当前
            $actionname = $this->ctrlname?$this->ctrlname:$this->defaultAction;
        }
		$actionname = strtolower($actionname);
		$first = substr($actionname,0,1);
		$main = substr($actionname,1);
		$actionname = 'do'.strtoupper($first).$main;
		$this->_action = $actionname;
        if(method_exists($this, 'prepare')) {
            $this->prepare();
        }
		ob_start();
		$json = $this->$actionname();
		$content = ob_get_contents();
		ob_clean();

		//js结尾的，作为jsonp来对待，自动转化为一个JS文件
		if(MRoute::$suffix == 'js'){
            echo 'define(function(){return '.json_encode($json,false).';})';
        }
		else if($json !== null){
		    if(is_string($json))
		        echo $json;
		    else
            {
                header('Content-type: application/json');
                echo json_encode($json);
            }
		} else {
			echo $content;
		}
	}
}