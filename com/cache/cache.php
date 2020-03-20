<?php
class MCache extends MComponent
{
    private static $_defaultConfig = array('name'=>'file','dirver'=>'file','directory'=>'/tmp/cache/','expire'=>86400);

    private static $_exprie;

    private static $_default_name;

    private static $_configs = array();

    private static $cachemap = array();

    /**
     * 'file' or 'memcache'
     * @param string $driver
     * @return MCache|null
     */
    public static function instance($driver=''){
        if(isset(self::$cachemap[$driver])){
            return self::$cachemap[$driver];
        }
        else
        {
            $driver || ($driver = self::$_default_name);

            if($driver && isset(self::$_configs[$driver])){
                return self::create(self::$_configs[$driver]);
            }
            return self::create(self::$_defaultConfig);
        }
    }

    public static function addConfig($config){
        if($config['name'] && !self::$_default_name){
            self::$_default_name = $config['name'];
        }
        self::$_configs[$config['name']] = $config;
    }

    public static function create($driverconfig){
        $drivername = 'file';
        if(isset($driverconfig['dirver'])){
            $drivername = $driverconfig['dirver'];
        } else if(isset($driverconfig['name'])){
            $drivername = $driverconfig['name'];
        }

        if(isset(self::$cachemap[$drivername])){
            return self::$cachemap[$drivername];
        }

        if(isset($driverconfig['expire'])){
            self::$_exprie = $driverconfig['expire'];
        }
        $cache = new MCache($drivername,$driverconfig);
        self::$cachemap[$drivername] = $cache;
        return $cache;
    }

    protected $_drivername;

    private $driver;

    public function __construct($drivername,$driverconfig){
        $this->_drivername = $drivername;
        $clsname = strtolower($drivername);
        $clsname = strtoupper(substr($clsname, 0,1)).substr($clsname, 1 ) . 'Driver';
        $filename = str_replace('\\', '/', dirname ( __FILE__ )) . '/drivers/' . $clsname . '.php';
        if(file_exists($filename)){
            require_once $filename;
        }
        else
            throw new MException("缓存驱动文件不存在".$filename);
        if(class_exists($clsname)){
            $this->driver = new $clsname($driverconfig);
        }
    }

    public function set($id,$value,$tags=null,$expire=0){
        if(isset(self::$_exprie) && $expire == 0){
            $expire = self::$_exprie;
        }
        return $this->driver->set($id,$value,$tags,$expire);
    }
    public function get($name){
        return $this->driver->get($name);
    }
    public function find($tag){
        return $this->driver->find($tag);
    }
    public function delete($id,$tag=FALSE){
        return $this->driver->delete($id,$tag);
    }
    public function delete_expired(){
        return $this->driver->delete_expired();
    }
}