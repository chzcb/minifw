<?php

/**
 * @Author: zhangcb
 * @Date:   2017-10-26 23:36:26
 * @Last Modified by:   zhangcb
 * @Last Modified time: 2017-10-27 16:51:13
 */

class Broker
{
	public $name;
	public $host;
	public $port;
	public $protocol;

	private $connection;

	public function __construct($config){
		$this->name = $config['name'];
		$this->host = $config['host'];
		$this->port = $config['port'];
		$this->protocol = isset($config['protocol'])?$config['protocol']:'tcp';
	}

	public function send($methodId,$args){
		if(!$this->connection){
			$this->connect();
			M::log("创建连接：".json_encode($this));
		}
		$this->connection->send(msgpack_pack(array('methodId'=>$methodId,'args'=>$args)));
		$ret = msgpack_unpack($this->connection->recv());
		return $ret;
	}

	private function connect(){
		$context = new ZMQContext ();
		$this->connection = new ZMQSocket ($context, ZMQ::SOCKET_REQ);
		$this->connection->connect($this->protocol."://".$this->host.":".$this->port);
	}
}

