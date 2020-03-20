<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/11/28
 * Time: 15:24
 */
class SessionManager
{
    static $defaultConfig;

    private static $type = "file";

    private static $session;

    public static function initConfig($config){
        self::$type = isset($config['type'])?$config['type']:self::$type;
        if(self::$type == 'file'){
            self::$session = new FileSession($config);
        } else if(self::$type == 'memcache'){
            self::$session = new MemcacheSession($config);
        } else if(self::$type == 'mysql'){
            self::$session = new MysqlSession($config);
        }
        if(self::$session){
            session_set_save_handler(
                self::$session,true
            );
        }
    }

    /**
     * 手工注册Session，通过接口方式
     * @param $session_id
     * @param $session_data
     */
    public static function register($session_id,$session_data){
        if(self::$session){
            self::$session->write($session_id);
        }
    }


}

class FileSession implements SessionHandlerInterface{
    private $savePath = "/tmp/session";
    private $savePre = 'sess_';

    public function __construct($config)
    {
        if(isset($config['save_path'])){
            $this->savePath = $config['save_path'];
        }
        if(isset($config['save_pre'])){
            $this->savePre = $config['save_pre'];
        }
    }

    function open($savePath, $sessionName)
    {
        $this->savePath = empty($savePath)?$this->savePath:rtrim($savePath,'/');
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }

        return true;
    }

    function close()
    {
        return true;
    }

    function read($id)
    {
        return (string)@file_get_contents("$this->savePath/".$this->savePre."$id");
    }

    function write($id, $data)
    {
        return file_put_contents("$this->savePath/".$this->savePre."$id", $data) === false ? false : true;
    }

    function destroy($id)
    {
        $file = "$this->savePath/".$this->savePre."$id";
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    function gc($maxlifetime)
    {
        foreach (glob("$this->savePath/".$this->savePre."*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
}

class MemcacheSession implements SessionHandlerInterface{

    private $_servers;

    private $_pre = "Session_Fw::";

    private $_flags = false;

    // 默认保存1天的会话
    private $_expire = 86400;

    public function __construct($config)
    {
        $this->_servers = isset($config['servers'])?$config['servers']:(isset($config['server'])?array($config['server']):array(array('127.0.0.1',11211)));
        if(class_exists('Memcache')){
            $this->_memcache = new Memcache();
            foreach($this->_servers as $ms){
                $this->_memcache->addServer($ms[0], $ms[1]);
            }
            if(isset($config['compress'])){
                $this->_flags = $config['compress']?MEMCACHE_COMPRESSED:false;
            }
            if(isset($config['expire'])){
                $this->_expire = $config['expire'];
            }
            if(isset($config['pre'])){
                $this->_pre = $config['pre'];
            }
        }
        else
        {
            throw new MException("请检查PHP配置，安装Memcache扩展");
        }
    }

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        $this->_memcache->delete($session_id);
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function open($save_path, $name)
    {
        return true;
    }

    public function read($session_id)
    {
        return $this->_memcache->get($this->_pre.$session_id);
    }

    public function write($session_id, $session_data)
    {
        return $this->_memcache->set($this->_pre.$session_id,$session_data,$this->_flags,$this->_expire);
    }
}

class MysqlSession implements SessionHandlerInterface{

    private $dbconn;

    private $_expire = 86400;

    function __construct($config)
    {
        if(isset($config['db'])){
            $this->dbconn = PDOConnection::addConfig($config['db']);
        }
        elseif(isset($config['dbconn'])){
            $this->dbconn = $config['dbconn'];
        }
        else
        {
            throw new MException("请检查Session数据库配置");
        }
        if(isset($config['expire'])){
            $this->_expire = $config['expire'];
        }
        $db = PDOConnection::instance($this->dbconn);
        $db->query("create table if not EXISTS `minifw_session` (
`session_id` varchar(64) not null,
`session_data` longtext not null,
`expire_time` int not null,
PRIMARY KEY (`session_id`),
KEY `idx_expire_key` (`expire_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        $db = PDOConnection::instance($this->dbconn);
        return $db->query("delete from `minifw_session` where `session_id`=?",array($session_id));
    }

    public function gc($maxlifetime)
    {
        $db = PDOConnection::instance($this->dbconn);
        return $db->query("delete from `minifw_session` where `expire_time`<?",array(time()));
    }

    public function open($save_path, $name)
    {
        return true;
    }

    public function read($session_id)
    {
        $db = PDOConnection::instance($this->dbconn);
        return $db->fetch_one_cell("select `session_data` from `minifw_session` where `session_id` = ? and `expire_time`>=?",array($session_id,time()));
    }

    public function write($session_id, $session_data)
    {
        $db = PDOConnection::instance($this->dbconn);
        $expire_time = time()+$this->_expire;
        $db->query("insert into `minifw_session` (`session_id`,`session_data`,`expire_time`) value (?,?,?) on duplicate key update `session_data` = ?,`expire_time` = ?",array($session_id,$session_data,$expire_time,$session_data,$expire_time));
        return true;
    }
}