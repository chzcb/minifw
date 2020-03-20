<?php

class ScriptController extends MController
{
    
    private $defaultAction = 'main';

    public function run()
    {
        $actionname = MyArgs::getAction($this->defaultAction);
        $actionname = strtolower($actionname);
        $this->_action = $actionname;
        $argv = MyArgs::getArgs();
        return call_user_func_array(array($this,$actionname),$argv);
    }

    public function __call($name,$params){
        Fw::log("没有找到方法：$name,当前入参：".StringUtils::json_encode($params));
    }
}