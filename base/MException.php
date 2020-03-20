<?php
/**
 * 基础异常类
 * @author Administrator
 *
 */
class MException extends Exception
{
	public function __construct($message, $params=null){
		if($params)
		$msg = strtr($message, $params);
		else 
		$msg = $message;
		parent::__construct($msg);
	}
}