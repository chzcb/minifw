<?php

/**
 * @Author: zhangcb
 * @Date:   2017-10-26 23:36:41
 * @Last Modified by:   zhangcb
 * @Last Modified time: 2017-10-27 13:38:13
 */
class Server
{
	private $port;

	public function __construct($port){
		$this->port = $port;
	}

	//tcp,ipc
	public $protocol = 'tcp';

	public function start(){
		$context = new ZMQContext (1);
		// Socket to talk to clients
		$responder = new ZMQSocket ($context, ZMQ::SOCKET_REP);
		
		$responder->bind ($this->protocol."://*:".$this->port);
		M::log("Start Server on ".$this->port);
		while(true) {
			//请求通过msgpack解压
			$req = msgpack_unpack($responder->recv());
			M::log("Received request: ".json_encode($req));
			$ret = M::exec($req['methodId'],$req['args']);

			M::log("Response:".json_encode($ret));
			$responder->send(msgpack_pack($ret));
		}
	}
}