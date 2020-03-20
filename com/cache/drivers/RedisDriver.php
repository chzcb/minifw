<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/12/29
 * Time: 09:10
 */

define('REDIS_TAG','php_cache');

class RedisDriver
{
    private static $_default_config = array(
        'timeout'=>0,
        'reserved'=>null,
        'retry_interval'=>0
    );

    private $redis;

    public function __construct($config)
    {
        if(!isset($config['server'])){
            throw new MException("缺少Redis的Server配置");
        }

        $server = ArrayUtils::defaults($config['server'],self::$_default_config);

        $host = $server['host'];
        $port = $server['port'];

        $timeout = $server['timeout'];
        $reserved = $server['reserved'];
        $retry_interval = $server['retry_interval'];

        try {
            $this->redis = new Redis();
            $this->redis->connect($host,$port,$timeout, $reserved, $retry_interval);
        }
        catch (Exception $e){
            throw new MException("Redis服务器连接失败:".$e->getMessage());
        }
    }


    public function set($id,$value,$expire=0){
        return $this->redis->hset(REDIS_TAG,$id,$value,$expire);
    }

    public function get($name){
        return (($ret = $this->_memcache->get($name)) === false)?null:$ret;
    }

    public function find($tag){
        if(isset($this->_tags[$tag]) && $results = $this->_memcache->get($this->_tags[$tag])){
            return $results;
        }
        else
        {
            return array();
        }
    }

    public function delete($id,$tag=FALSE){
        $this->_tags_changed = true;
        if($id === true){
            if($status = $this->_memcache->flush()){
                $this->_tags = array();
                sleep(1);
            }
            return $status;
        }
        elseif($tag === true){
            if(isset($this->_tags[$id])){
                foreach($this->_tags[$id] as $_id){
                    $this->_memcache->delete($_id);
                }
                unset($this->_tags[$id]);
            }
            return true;
        }
        else
        {
            foreach($this->_tags as $tag => $_ids){
                if(isset($this->_tags[$tag][$id])){
                    unset($this->_tags[$tag][$id]);
                }
            }
            return $this->_memcache->delete($id);
        }
    }

    public function delete_expired(){
        $this->_tags_changed = true;
        foreach($this->_tags as $tag => $_ids){
            foreach ($_ids as $id){
                if(!$this->_memcache->get($id)){
                    unset($this->_tags[$tag][$id]);
                }
            }
            if(empty($this->_tags[$tag])){
                unset($this->_tags[$tag]);
            }
        }
        return true;
    }
}