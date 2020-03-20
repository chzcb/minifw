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
class AMQPClient
{
    static $defaultConfig = array('name'=>'default','host'=>'127.0.0.1','port'=>5672,'user'=>'guest','passwd'=>'guest');

    static $_allConfig;
    static $_allClient = array();

    public static function create($config){
        if(isset(self::$_allClient[$config['name']])){
            return self::$_allClient[$config['name']];
        }
        $client = new AMQPClient($config);
        self::$_allClient[$config['name']] = $client;
        return $client;
    }

    public static function addConfig($config){
        AMQPClient::$_allConfig[$config['name']] = $config;
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
    private $queue;

    public function __construct($config)
    {
        $this->connection = new AMQPStreamConnection($config['host'],$config['port'],$config['user'],$config['passwd']);
        $this->channel = $this->connection->channel();
        $this->queue = $this->channel->queue_declare("php_amqp_client_queue_".uniqid(),false,false,true);
    }

    public function subscribe($callback, $exchange, ...$bindingKeys){
        foreach ($bindingKeys as $bindingKey){
            $this->channel->queue_bind($this->queue, $exchange,$bindingKey);
        }
        $this->queue->consume($callback);
    }

}