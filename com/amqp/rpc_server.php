<?php

require_once FW_PATH.'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/12/26
 * Time: 14:11
 */
class AMQPRpcServer
{
    static $defaultConfig = array('name' => 'default', 'host' => '127.0.0.1', 'port' => 5672, 'user' => 'guest', 'passwd' => 'guest');

    static $_allConfig;
    static $_allClient = array();

    public static function create($config)
    {
        if (isset(self::$_allClient[$config['name']])) {
            return self::$_allClient[$config['name']];
        }
        $client = new AMQPRpcServer($config);
        self::$_allClient[$config['name']] = $client;
        return $client;
    }

    public static function addConfig($config)
    {
        AMQPRpcServer::$_allConfig[$config['name']] = $config;
        return $config['name'];
    }

    public static function instance($instance = 'default')
    {
        if (isset(self::$_allClient[$instance])) {
            return self::$_allClient[$instance];
        } else {
            if (isset(self::$_allConfig[$instance])) {
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
    }

    public function addListener($queueName,$callback){
        $this->channel->queue_declare($queueName,false,false,false,false);
        $this->channel->basic_qos(null,1,false);
        $this->channel->basic_consume($queueName,'',false,false,false,false,$callback);
    }

    public function start(){
        while(count($this->channel->callbacks)){
            $this->channel->wait();
        }
        $this->channel->close();
        $this->connection->close();
    }


}