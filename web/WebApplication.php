<?php

class WebApplication extends MApplication {
	public $layout = 'main';

    /**
     * @var $_session MSession
     */
	private $_session;
	
	public $basePath;
	
	private $_defaultController = 'default';
	
	private $_defaultAction = 'index';

	private $allow_origin = array();
	
	private $_cache = false;
	
	public function __construct(){
		Fw::setApplication($this);
		$this->init();
		$this->basePath = MRoute::getBasePath().'/web';
	}
	
	protected function init(){
		header("Content-type: text/html; charset=".$this->charset);
		Fw::addInclude('MWebException',FW_PATH . '/web/MWebException.php');
		parent::init();
	}
	
	public function initSession(){
		$this->_session = new MSession();
	}
	
	public function initComponents() {
		parent::initComponents();
		$this->setComponent('MRoute', '/web/route/MRoute.php');
		$this->setComponent('MAuth', '/web/security/MAuth.php');
		$this->setComponent('MSession', '/web/security/MSession.php');
        $this->setComponent('SessionManager', '/web/session/SessionManager.php');
		$this->setComponent('SmartyView','/web/html/SmartyView.php');
	}
	
	public function getSession(){
		return $this->_session;
	}
	
	public function getController(){
		return $this->_controller;
	}
	
	public function setConfig($config){
		require $config;
		if (isset ( $config['route'] )) {
			$route = $config['route'];
			if(isset($route['showScriptName']))
				MRoute::$showScriptName = $route ['showScriptName'];
		}
		if(isset($config['controller'])){
			$ctrlconfig = $config['controller'];
			if(isset($ctrlconfig['default'])){
				$this->_defaultController = $ctrlconfig['default'];
			}
			if(isset($ctrlconfig['action'])){
				$this->_defaultAction = $ctrlconfig['action'];
			}
		}
		if(isset($config['web'])){
			$webconfig = $config['web'];
			if(isset($webconfig['path'])){
				if(isset($webconfig['path']))
					$this->setBasePath($webconfig['path']);
				else
					$this->setBasePath();
			}
		}
		if(isset($config['cache'])){
			MCache::addConfig($config['cache']);
			$this->_cache = true;
		}
		if(isset($config['db'])){
			if(!isset($config['db']['name'])){
				foreach ($config['db'] as $dbconfig) {
					DBConnection::addConfig($dbconfig);
				}
			}
			else
				DBConnection::addConfig($config['db']);
		}
		if(isset($config['allow_origin'])){
            foreach ($config['allow_origin'] as $origin) {
                $this->allow_origin[] = $origin;
		    }
        }
        if(isset($config['session'])){
		    SessionManager::initConfig($config['session']);
        }
        if(isset($config['ini_set'])){
            foreach ($config['ini_set'] as $key=>$value){
                ini_set($key,$value);
            }
        }
        if(isset($config['debug'])){
            if($config['debug'] == false){
                xdebug_disable();
            }
        }
	}
	
	public function setBasePath($path = '/web'){
		$path = trim($path,'/');
		$this->basePath = rtrim(rtrim(MRoute::getBasePath(),'/')."/".$path,'/');
	}

	protected function checkOrigin(){
	    $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
	    if(in_array($origin,$this->allow_origin)){
            header('Access-Control-Allow-Credentials:true');
            header('Access-Control-Allow-Origin:'.$origin);
            header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
        }
    }

	protected function createController(){
		if(!file_exists($this->basePath)){
			throw new MException('Base Path not Exist:'.$this->basePath);
		}
		$ctrlname = MRoute::getController($this->_defaultController);
        $ctrlclsname = strtolower($ctrlname);
        $ctrlclsname = strtoupper(substr($ctrlclsname,0,1)).substr($ctrlclsname,1).'Controller';
        $ctrlfile = $this->basePath.'/controller/'.$ctrlclsname.'.php';

        $isdefault = false;

		//如果当前控制器不存在，那么就用默认的，并修改action
		if(!file_exists($ctrlfile)){
            $ctrlclsname = strtoupper(substr($this->_defaultController,0,1)).substr($this->_defaultController,1).'Controller';
            $ctrlfile = $this->basePath.'/controller/'.$ctrlclsname.'.php';
            $isdefault = true;
//			throw new MException('控制器的类路径不存在，请创建'.$ctrlfile);
		}
		require_once $ctrlfile;
		$this->_controller = new $ctrlclsname();
		if($isdefault)
		    $this->_controller->ctrlname = $ctrlname;
		$this->_controller->session = $this->_session;
		$this->_controller->setLayoutPath($this->basePath.'/layout');
		$this->_controller->setModelPath($this->basePath.'/model');
		$this->_controller->setViewPath($this->basePath.'/view');
		$this->_controller->_cache = ($this->_cache);
	}

	public function run($config=null)
	{
		try {
			if($config){
				$this->setConfig($config);
			}
			//检查能否跨域
			$this->checkOrigin();
			//检查是否是机器人，机器人就不创建Session
            if(!MRoute::isRobot())
                $this->initSession();
			$this->createController();
			$this->_controller->run();
        } catch (MWebException $e) {
            if ($this->type == 'JSON') {
                ob_clean();
                header('Content-type: application/json');
                echo json_encode(array('error_code' => $e->error_code, 'error_level'=>2,'error_info' => $e->error_info));
            } else {
                echo $e->getMessage(), "\n";
                echo $e->getTraceAsString();
            }
        } catch (MException $e){
            if ($this->type == 'JSON') {
                ob_clean();
                header('Content-type: application/json');
                echo json_encode(array('error_code' => '9999','error_level'=>1, 'error_info' => $e->getMessage()));
            } else {
                echo $e->getMessage(), "\n";
                echo $e->getTraceAsString();
            }
		} catch (Exception $e) {
            echo $e;
		}
	}
	public function test($config=null)
	{
		try {
            if ($config) {
                $this->setConfig($config);
            }
            $this->createController();
            $this->_controller->run();
            Fw::debugEnding();
        } catch (MWebException $e){
		    if($this->type == 'JSON'){
		        echo json_encode($e);
            }
		} catch (Exception $e) {
			Fw::e($e);
			Fw::debugEnding();
		}
	}
}
