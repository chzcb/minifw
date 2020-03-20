<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/12/1
 * Time: 10:09
 */

define('NOT_LOGIN','1001');
define('AUTH_ERROR','1002');


class MWebException extends RuntimeException
{
    public $error_code;
    public $error_info;

    public function __construct($error_code,$error_info=""){
        parent::__construct($error_code+":"+$error_info);
        $this->error_code = $error_code;
        $this->error_info = $error_info;
    }
}