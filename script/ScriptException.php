<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/12/20
 * Time: 11:18
 */

class ScriptException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


}