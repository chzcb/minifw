<?php
class ScriptApplication extends MApplication {
    
    private $_defaultController = 'default';
	
    public function setConfig($config){

        require $config;

        $this->config = $config;

        if(isset($config['script'])){
            $scriptconfig = $config['script'];
            if(isset($scriptconfig['path'])){
                if(isset($scriptconfig['path']))
                    $this->setBasePath($scriptconfig['path']);
                    else
                        $this->setBasePath();
            }
            if(isset($scriptconfig['controller'])){
                $this->_defaultController = $scriptconfig['controller'];
            }
        }
        if(isset($config['db'])){
            if(!isset($config['db']['name'])){
                foreach ($config['db'] as $dbcfg) {
                    DBConnection::addConfig($dbcfg);
                }
            } else {
                DBConnection::addConfig($config['db']);
            }
        }
        if(isset($config['cache'])){
            MCache::addConfig($config['cache']);
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
        if(isset($config['amqp'])){
            if(!isset($config['amqp']['name'])){
                foreach ($config['amqp'] as $cfg) {
                    AMQPRpcServer::addConfig($cfg);
                }
            } else {
                AMQPRpcServer::addConfig($config['amqp']);
            }
        }
    }
    
    public function setBasePath($path = '/app'){
        $path = rtrim($path,'/');
        $this->basePath = MyArgs::getBasePath().$path;
    }
    
    protected function initComponents(){
        $this->setComponent('MyArgs', '/script/route/MyArgs.php');
        $this->setComponent('ScriptException', '/script/ScriptException.php');
    }
    
    public function __construct(){
        Fw::setApplication($this);
        $this->init();
        $this->basePath = MyArgs::getBasePath().'/app';
    }
    
    protected function createController(){
        if(!file_exists($this->basePath)){
            throw new MException('基础脚本目录不存在，请检查配置文件'.$this->basePath);
        }
        $ctrlname = MyArgs::getController($this->_defaultController);
        $ctrlclsname = strtolower($ctrlname);
        $ctrlclsname = strtoupper(substr($ctrlclsname,0,1)).substr($ctrlclsname,1).'Controller';
        $ctrlfile = $this->basePath.'/controller/'.$ctrlclsname.'.php';
        if(!file_exists($ctrlfile)){
            throw new MException('控制器的类路径不存在，请创建'.$ctrlfile);
        }
        require_once $ctrlfile;
        $this->_controller = new $ctrlclsname($ctrlname);
        $this->_controller->setModelPath($this->basePath.'/model'); 
    }

    public function run($config=null) {

        //配置文件
        try {
            if($config){
                $this->setConfig($config);
            }
            $this->createController();
            $this->_controller->run();
        } catch (Exception $e) {
            echo $e->getMessage(),"\n";
            echo $e->getTraceAsString(),"\n";
        }
        
        
        
        
    }
}