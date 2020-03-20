<?php
/**
 *
 * @auther 张昌彪
 * @desc 这是我业务时间整理的一个PHP框架，希望大家多多支持
 * @version 1.2.1.1712
 *
 *
 * 这是一个PHP的简单框架，方便快捷日常的php搭建网站或者后台执行
 * 里面提供了很多比较方便的应用：
 * 1、WebApplication 网站的应用
 *      1) WebController 页面控制器
 *      2) JSONController Ajax控制器
 * 2、ScriptApplication
 *      1) ScriptController 脚本控制器 里面的方法可以直接通过命令参数 -a 来调用
 *
 * 3、com公共库
 *      1） cache 缓存模块，目前支持文件缓存和Memcache缓存
 *      2） db 数据库模块、目前支持Mysql、Mysqli、PDO等php的组件，其中PDO可以连接mysql之外的（目前没尝试）
 *      3） http 访问http的工具
 *      4） utils 工具汇总：string、array、date、curl、rsa、xml等等
 *
 */



defined('FW_PATH') or define('FW_PATH', dirname(__FILE__));

defined('FW_DEBUG') or define('FW_DEBUG', true);

date_default_timezone_set('Asia/Shanghai');

class Fw
{

    public static $starttime;

    /**
     * @var $_app MApplication
     */
    protected static $_app;

    private static $_other_autoload = array();

    /**
     * 每个页面加载的时候，根据条件配置进去可能引用的类的映射
     * 
     * @var unknown_type
     */
    public static $classMap = array();

    /**
     * 可能引用的核心类映射
     * 
     * @var unknown_type
     */
    private static $_coreClass = array(
        'MApplication' => '/base/MApplication.php',
        'MComponent' => '/base/MComponent.php',
        'MException' => '/base/MException.php',
        'MController' => '/base/MController.php',
        'MModel' => '/base/MModel.php',
        'MView' => '/base/MView.php',
        'MRoute' => '/web/route/MRoute.php',
        'WebApplication' => '/web/WebApplication.php',
        'WebController' => '/web/WebController.php',
        'JSONController' => '/web/JSONController.php',
        'AjaxController' => '/web/AjaxController.php',
        'ApiController' => '/web/ApiController.php',
        'HttpRequest'=>'/com/http/HttpRequest.php',
        'MCache' => '/com/cache/cache.php',
        'DBConnection' => '/com/db/DBConnection.php',
        'PDOConnection' => '/com/db/PDOConnection.php',
        'MWebUser' => '/web/auth/MWebUser.php',
        'ScriptApplication' => '/script/ScriptApplication.php',
        'ScriptController' =>'/script/ScriptController.php',
        'AMQPRpcClient'=>'/com/amqp/rpc_client.php',
        'AMQPRpcServer'=>'/com/amqp/rpc_server.php',
        'AMQPClient'=>'/com/amqp/client.php'
    );

    private static $_includeClass = array();

    public static function addAutoLoad($autoload_function){
        self::$_other_autoload[] = $autoload_function;
    }

    public static function getVersion()
    {
        return '1.0.0';
    }
    
    public static function getCorePath()
    {
        return dirname(__FILE__);
    }

    public static function createApplication($type = 'web')
    {
        if($type == 'web')
            self::$_app = new WebApplication();
        else if($type == 'script')
            self::$_app = new ScriptApplication();
        return self::$_app;
    }

    public static function createScriptApplication()
    {
        self::$_app = new ScriptApplication();
        return self::$_app;
    }

    public static function app()
    {
        return self::$_app;
    }

    public static function setApplication($app)
    {
        if (self::$_app === null || $app === null)
            self::$_app = $app;
        else
            throw new MException("应用唯一，不能多次设置");
    }

    public static function addInclude($name, $class)
    {
        self::$_includeClass[$name] = $class;
    }

    public static function autoload($clsName)
    {
        if (isset(self::$_coreClass[$clsName])) {
            require_once FW_PATH . self::$_coreClass[$clsName];
        } elseif (isset(self::$_includeClass[$clsName])) {
            require_once self::$_includeClass[$clsName];
        } elseif (strrchr($clsName,'Utils') == 'Utils'){
            require_once FW_PATH.'/com/utils/'.strtolower(substr($clsName,0,-5)).'.php';
        } elseif (strrchr($clsName,'Action') == 'Action'){
            require_once self::$_app->basePath.'/action/'.$clsName.'.php';
        } else if(self::$_other_autoload){
            foreach (self::$_other_autoload as $autoload_func) {
                call_user_func_array($autoload_func,array($clsName));
            }
        }

    }

    public static function e($e)
    {
        if(PHP_SAPI !== 'cli'){
            if(self::$_app->type == 'WEB'){
                echo '<div style="margin:10px;background-color:#ea7b61">';
                echo '<h1 style="font-size:18px;line-height:20px;"><font color="#f50000">' . $e->getMessage() . '</font></h1>';
                if (FW_DEBUG) {
                    echo '<ul style="list-style:none;padding:0px;">';
                    foreach ($e->getTrace() as $el) {
                        echo '<li><font color="#007700">Function </font><strong>' . $el['function'] . '</strong> ' . $el['file'] . '(' . $el['line'] . ')</li>';
                    }
                    echo '</ul>';
                }
                echo '</div>';
            }
            else
            {
                echo $e->getTraceAsString(),"\n";
            }
        }
        else{
            return false;
        }
    }

    public static function log($content,$level='debug')
    {
        if($level == 'warn'){
            echo "\033[35m";
        } else if($level == 'error'){
            echo "\033[31m";
        } else if($level == 'success'){
            echo "\033[32m";
        }
        if($content === false){
            print_r('false');
        } else
        print_r($content);
        if($level == 'warn' || $level == 'error' || $level == 'success'){
            echo "\033[0m";
        }
        echo "\n";
    }

    public static function debugEnding()
    {
        echo '<div class="navbar-fixed-bottom"><hr>';
        echo '耗时：';
        printf('%01.2fms', 1000 * (self::getSysTime() - self::$starttime));
        echo '，占用内存：';
        printf('%01.2fK', memory_get_usage() / 1024);
        echo '</div>';
    }

    public static function debug($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

    public static function redirect($url)
    {
        if (strpos($url, "http://") === 0 || strpos($url, "https://") === 0) {
            header("Location:" . $url);
        } else {
            header("Location:" . MRoute::getBaseUrl() . '/' . trim($url, '/'));
        }
        exit();
    }

    public static function p($var, $default = "")
    {
        if (isset($var) && ! empty($var)) {
            echo $var;
        } else {
            echo $default;
        }
    }

    public static function getSysTime()
    {
        $time = microtime();
        $arr = explode(" ", $time);
        return $arr[0] + $arr[1];
    }

}

Fw::$starttime = Fw::getSysTime();

spl_autoload_register(array(
    'Fw',
    'autoload'
));