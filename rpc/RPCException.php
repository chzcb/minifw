<?php

/**
 * @Author: zhangcb
 * @Date:   2017-10-26 13:13:53
 * @Last Modified by:   zhangcb
 * @Last Modified time: 2017-10-26 13:15:30
 */
class RPCException extends Exception
{
	public function __construct($message, $params=null){
		if($params)
		$msg = strtr($message, $params);
		else 
		$msg = $message;
		parent::__construct($msg);
	}
}