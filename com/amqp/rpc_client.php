<?php

require_once FW_PATH.'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/12/26
 * Time: 11:04
 */
class AMQPRpcClient
{
    static $defaultConfig = array('name'=>'default','host'=>'127.0.0.1','port'=>5672,'user'=>'guest','passwd'=>'guest');

    static $_allConfig;
    static $_allClient = array();

    public static function create($config){
        if(isset(self::$_allClient[$config['name']])){
            return self::$_allClient[$config['name']];
        }
        $client = new AMQPRpcClient($config);
        self::$_allClient[$config['name']] = $client;
        return $client;
    }

    public static function addConfig($config){
        AMQPRpcClient::$_allConfig[$config['name']] = $config;
        return $config['name'];
    }

    public static function instance($instance='default'){
        if(isset(self::$_allClient[$instance])){
            return self::$_allClient[$instance];
        }
        else
        {
            if(isset(self::$_allConfig[$instance])){
                $config = self::$_allConfig[$instance];
            } else {
                $config = self::$defaultConfig;
            }
            return self::create($config);
        }

    }


    private $connection;
    private $channel;

    public function __construct($config)
    {
        $this->connection = new AMQPStreamConnection($config['host'],$config['port'],$config['user'],$config['passwd']);
        $this->channel = $this->connection->channel();

        list($this->callbackQueue, ,) = $this->channel->queue_declare("", false, false, true, false);
        $this->channel->basic_consume($this->callbackQueue, '', false, false, false, false, array($this, 'onResponse'));
    }

    public function onResponse($rep)
    {
        if ($rep->get('correlation_id') == $this->corrId) {
            $this->response = $rep->body;
        }
    }

    public function call($queueName, $content)
    {
        $this->response = null;
        $this->corrId = uniqid();

        $msg = new AMQPMessage((string) $content, array(
            'correlation_id' => $this->corrId,
            'reply_to' => $this->callbackQueue
        ));

        $this->channel->basic_publish($msg, '', $queueName);
        while (!$this->response) {
            $this->channel->wait();
        }
        return $this->response;
    }


}