<?php
/**
 * 调用存储过程
 * @authors ChangeBiao Zhang (chzcb@vip.qq.com)
 * @date    2017-10-26 10:44:40
 * @version 0.0.1
 */

class ProcedureCaller {

	public static function call($connName,$method,$args){
		M::log('ProcedureCaller::call '.$method);
		M::log($args);

		//获得方法对于的数据库连接
		$db = PDOConn::instance($connName);
		$ret = call_procedure_rows_witherror($db,$method,$args,$error);
		if(!$ret && $error){
			return $error;
		}
		return $ret;
	}
}