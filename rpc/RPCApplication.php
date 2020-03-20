<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/12/19
 * Time: 13:11
 */

class RPCApplication extends MApplication
{
    public function __construct(){
        Fw::setApplication($this);
        $this->init();
    }

    protected function initComponents(){

    }

    public function setConfig($config){

    }
}