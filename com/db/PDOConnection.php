<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/11/10
 * Time: 13:26
 */
define('MAX_CACHE_STMT',100);

class PDOConnection
{
    private static $conns = array();

    public static function create($driverConfig){
        //如果已经定义过就抛出异常
        if(isset(self::$conns[$driverConfig['name']])){
            throw new Exception("已经定义数据库连接：$driverConfig[name]");
        }
        try {
            $dsn = "mysql:dbname=".$driverConfig['db'].";host=".$driverConfig['host'].";";
            $user = $driverConfig['user'];
            $password = $driverConfig['passwd'];

            $dbCon = new PDO($dsn, $user, $password,array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '".(isset($driverConfig['charset'])?$driverConfig['charset']:"UTF8")."';"
                )
            );
            $ret = new PDOConnection($dbCon);
            self::$conns[$driverConfig['name']] = $ret;
            return $ret;
        } catch (PDOException $e) {
            print 'Connection failed: '.$e->getMessage();
            exit;
        }
    }

    public static function addConfig($config){
        DBConnection::$_allConfig[$config['name']] = $config;
        return $config['name'];
    }

    public static function instance($name='default'){
        if(isset(self::$conns[$name])){
            return self::$conns[$name];
        }
        else if(DBConnection::$_allConfig[$name]){
            return self::create(DBConnection::$_allConfig[$name]);
        }
        throw new Exception("找不到连接：$name");
    }

    private $db;

    public function __construct($dbConn)
    {
        $this->db = $dbConn;
    }

    public function fetch_rows($query,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
        return false;
    }

    public function fetch_one($query,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
        return false;
    }

    public function fetch_column($query,$columnIndex=0,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
            $count = $stmt->rowCount();
            $list = array();
            while($count-->0){
                array_push($list,$stmt->fetchColumn($columnIndex));
            }
            return $list;
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
        return false;
    }

    public function fetch_list($query,$args=array())
    {
        $row_1 = self::fetch_column($query,0,$args);
        $row_2 = self::fetch_column($query,1,$args);
        $number = count($row_1);
        $list = array();
        for($i=0;$i<$number;$i++)
        {
            $list[$row_1[$i]] = $row_2[$i];
        }
        return $list;
    }

    public function fetch_one_cell($query,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
            return $stmt->fetchColumn(0);
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
        return false;
    }

    public function start_transaction(){
        $this->db->beginTransaction();
    }

    public function commit(){
        $this->db->commit();
    }

    public function rollback(){
        $this->db->rollback();
    }

    public function check($query,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
            return $stmt->rowCount()>0;
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
        return false;
    }

    public function insert($query,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
            $id = $this->db->lastInsertId();
            if($id){
                return $id;
            }
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
        return false;
    }

    public function update($query,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
            return $stmt->rowCount();
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
        return false;
    }

    public function delete($query,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
            return $stmt->rowCount();
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
        return false;
    }

    public function query($query,$args=array()){
        $stmt = $this->prepare_query($query,$args);
        if($stmt->execute()){
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=${query},args:".StringUtils::json_encode($args));
        }
    }

    /**
     * 批量执行语句
     * @param $query
     * @param array $args
     */
    public function batchQuery($query,$args=array()){

        if($args){
            $first = array_shift($args);
            //如果是用问好的
            if(strpos($query,'?')>-1){
                foreach ($first as $index=>&$value) {
                    $query = preg_replace("`\\?`", ':' . $index, $query, 1);
                }
            }
            $stmt = $this->db->prepare($query);
            foreach ($first as $index=>&$value) {
                $stmt->bindParam(':'.$index,$value);
            }
            $stmt->execute();
            foreach ($args as $item){
                foreach ($first as $index=>&$value) {
                    $value = $item[$index];
//                    $stmt->bindParam(':'.$index,$item[$index]);
                }
                $stmt->execute();
            }
        }
    }

    /**
     * 描述表字段
     * @param $table_name
     */
    public function fetch_fields($table_name){
        $stmt = $this->prepare_query("desc ".$table_name,array());
        if($stmt->execute()){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            $errorInfo = $stmt->errorInfo();
            throw new MException("[$errorInfo[1]]:$errorInfo[2],query=desc ${table_name}");
        }
    }


    private $cache_stmt = array();
    private $cache_query = array();

    function prepare_query($query,$args){
        if(isset($this->cache_stmt[$query])){
            $stmt =  $this->cache_stmt[$query];
        }
        else
        {
            //如果是用问好的
            if(strpos($query,'?')>-1){
                foreach ($args as $index=>$value) {
                    $query = preg_replace("`\\?`",':'.$index,$query,1);
                }
            }
            $stmt = $this->db->prepare($query);
            $this->cache_stmt[$query] = $stmt;
            array_push($this->cache_query,$query);

            if(count($this->cache_query)>MAX_CACHE_STMT){
                $remove = array_shift($this->cache_query);
                $this->cache_stmt[$remove] = null;
                unset($this->cache_stmt[$remove]);
            }
        }

        if($args){
            foreach ($args as $index=>&$value) {
                $stmt->bindParam(':'.$index,$value);
            }
        }
        return $stmt;
    }

}