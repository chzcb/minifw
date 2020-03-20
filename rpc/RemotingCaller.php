<?php

/**
 * @Author: zhangcb
 * @Date:   2017-10-26 13:10:22
 * @Last Modified by:   zhangcb
 * @Last Modified time: 2017-10-27 13:05:20
 */
class RemotingCaller
{
	private static $routings = array();

	private static $brokers = array();

	/**
	 * 增加路由规则
	 */
	public static function addRouting($rule,$brokerNames){
		self::$routings[$rule] = $brokerNames;
	}

	public static function addBroker($broker){
		M::log('Add Broker:'.$broker->name);
		self::$brokers[$broker->name] = $broker;
	}

	public static function call($methodId,$args){
		$brokers = array();
		//默认没有配置路由，就选第一个
		if(empty(self::$routings)){
		    $brokers[] = self::$brokers[array_rand(self::$brokers)];
        }
        else
        {
            foreach (self::$routings as $key => $brokerNames) {
                if(preg_match("`".$key."`",$methodId)){
                    M::log("匹配：".$key);
                    M::log("brokerNames:".json_encode($brokerNames));
                    foreach ($brokerNames as $brokerName) {
                        if(isset(self::$brokers[$brokerName])){
                            array_push($brokers,self::$brokers[$brokerName]);
                        }
                    }
                }
            }
        }

		//有路由
		if(count($brokers)>0){
			$broker = $brokers[0];
			return $broker->send($methodId,$args);
		} else {
			M::log('发送消失失败：'.$methodId." 没有匹配路由。");
		}
		return false;
	}
}