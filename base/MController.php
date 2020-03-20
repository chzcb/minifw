<?php

abstract class MController extends MComponent {
    public $session;

	public $ctrlname;

	abstract public function run();
	public function setViewPath($path){
		$this->viewPath = rtrim($path,'/');
	}
	public function setLayoutPath($path){
		$this->layoutPath = rtrim($path,'/');
	}
	public function setModelPath($path){
		$this->modelPath = rtrim($path,'/');
	}
	protected $viewPath;
	protected $layoutPath;
	protected $modelPath;
	public function createView($viewname,$data=null){
		$viewCls = $this->getView($viewname,$data);
		return $viewCls->render();
	}
	public function getView($viewname,$data=null){
		$viewfile = $this->viewPath.'/'.$viewname.'.php';
		$layout = $viewname;
		//没有找到视图类，就使用默认的MView
		if(!file_exists($viewfile)){
			$viewfile = $this->viewPath.'/default.php';
			if(!file_exists($viewfile)){
				throw new MException('Can not find the View File:'.$viewfile);
			} else {
				require_once $viewfile;
				$viewCls = new DefaultView($data);	
			}
		}
		else
		{
			require_once $viewfile;
			$pos = strrpos($viewname,'/');
			if($pos>-1){
				$viewname = substr($viewname,$pos+1);
			}
			$viewname = strtolower($viewname);
			$viewname = strtoupper(substr($viewname,0,1)).substr($viewname,1).'View';
			$viewCls = new $viewname($data);	
		}
		
		$viewCls->setLayout($layout);
		$viewCls->setLayoutPath($this->layoutPath.'/view');
		$viewCls->init($this);
		return $viewCls;
	}
	public function createModel($modelname,$data=null){
		$modelfile = $this->modelPath.'/'.$modelname.'.php';
		if(!file_exists($modelfile)){
			throw new MException('Cannot find Model File:'.$modelfile);
		}
		require_once $modelfile;
		$pos = strrpos($modelname,'/');
		if($pos>-1){
			$modelname = substr($modelname,$pos+1);
		}
		$modelname = strtolower($modelname);
		$modelname = strtoupper(substr($modelname,0,1)).substr($modelname,1).'Model';
		return new $modelname($data);
	}
}

?>