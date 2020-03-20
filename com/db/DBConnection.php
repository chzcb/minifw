<?php

class DBConnection extends MComponent
{
	private static $_defaultConfig = array('type'=>'mysql','name'=>'default','host'=>'127.0.0.1:3306','user'=>'root','passwd'=>'','db'=>'test','charset'=>'utf8');

	public static $_allConfig = array();

	public static function create($driverconfig){
		$drivername = $driverconfig['name'];
		if(isset(self::$_instances[$drivername])){
			return self::$_instances[$drivername];
		}
		$db = new DBConnection($driverconfig);
		self::$_instances[$drivername] = $db;
		return $db;
	}

	public static function addConfig($config){
        self::$_allConfig[$config['name']] = $config;
        return $config['name'];
    }

	private static $_instances;
	
	public static function instance($driver='default'){
		if(isset(self::$_instances[$driver])){
			return self::$_instances[$driver];
		}
		else if(self::$_allConfig[$driver]){
		    return self::create(self::$_allConfig[$driver]);
        }
		else
		{
			if($driver == 'default'){
		    	return self::create(self::$_defaultConfig);
			}
			else
			throw new MException("没有找到该数据库配置");
		}
	}
	
	private $driver;

	public function getDriver(){
	    return $this->driver;
    }
	
	public function __construct($driverconfig){
		$drivername = $driverconfig['type'];
		$clsname = strtolower($drivername);
		$clsname = strtoupper(substr($clsname, 0,1)).substr($clsname, 1 ) . 'Driver';
		$filename = str_replace('\\', '/', dirname ( __FILE__ )) . '/drivers/' . $clsname . '.php';
		if(file_exists($filename)){
			require_once $filename;
		}
		else
			throw new MException("数据库驱动文件不存在".$filename);
		if(class_exists($clsname)){
            $this->driver = new $clsname($driverconfig);
		}
	}
	
 /**
     * 返回数据库查询结果，作为最大的数组返回
     * fetch_rows()
     * 
     * @param mixed $str
     * @return
     */
    public function fetch_rows($str)
    {
    	return $this->driver->fetch_rows($str);
    }
    public function check($str){
    	return $this->driver->check($str);
    }
    /**
     * 返回查询结构的第NUM条的数据，默认是第一条
     * fetch_column()
     * 
     * @param mixed $str
     * @param integer $NUM
     * @return
     */
    public function fetch_one($str,$NUM=0)
    {
    	return $this->driver->fetch_one($str);
    }
    /**
     * 返回查询结构的第NUM列的数据，默认是第一列
     * fetch_column()
     * 
     * @param mixed $str
     * @param integer $NUM
     * @return
     */
    public function fetch_column($str,$NUM=0)
    {
    	return $this->driver->fetch_column($str,$NUM);
    }
    /**
     * 获得查询语句的第一条，第一列的数值
     * fetch_one_cell()
     * 
     * @param mixed $str
     * @return
     */
    public function fetch_one_cell($str)
    {
        return $this->driver->fetch_one_cell($str);
    }
    /**
     * 返回一个数组，第一列作为key，第二列作为value，其他列有或没有都没有影响
     * fetch_list()
     * 
     * @param mixed $str
     * @return
     */
    public function fetch_list($str)
    {
    	return $this->driver->fetch_list($str);
    }
    /**
     * 
     * 
     * @param mixed $str
     * @return
     */
    public function query($str)
    {
    	return $this->driver->query($str);
    }

    public function call_rows($str){
        return $this->driver->call_rows($str);
    }

    /**
     * 描述表字段
     * @param $table_name
     */
    public function fetch_fields($table_name){
        return $this->driver->fetch_fields($table_name);
    }

    public function prepare($str){
        return $this->driver->prepare($str);
    }

    /**
     * 
     * update()
     * 
     * @param mixed $str
     * @return
     */
    public function update($str)
    {
    	return $this->driver->update($str);
    }
    /**
     * 插入数据库的函数
     * 
     * @param mixed $str
     * @return
     */
    public function insert($str)
    {
    	return $this->driver->insert($str);
    }
    
}