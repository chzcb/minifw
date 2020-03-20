<?php
class MRoute
{
	private static $_scriptUrl;
	
	private static $_requestUrl;
	
	private static $_refererUrl;
	
	private static $_hostInfo;
	
	private static $_baseUrl;
	
	private static $_basePath;
	
	private static $_paths;

	public static $suffix;
	
	public static $showScriptName = false;
	
	public static function getRobot(){
		if (empty($_SERVER['HTTP_USER_AGENT']))
		{
			return false;
		}
		$searchEngineBot = array(
			'googlebot'=>'google',
			'mediapartners-google'=>'google',
			'baiduspider'=>'baidu',
			'msnbot'=>'msn',
			'yodaobot'=>'yodao',
			'youdaobot'=>'yodao',
			'yahoo! slurp'=>'yahoo',
			'yahoo! slurp china'=>'yahoo',
			'iaskspider'=>'iask',
			'sogou web spider'=>'sogou',
			'sogou push spider'=>'sogou',
			'sosospider'=>'soso',
			'spider'=>'other',
			'crawler'=>'other',
		);
		
		$spider = strtolower($_SERVER['HTTP_USER_AGENT']);
		foreach ($searchEngineBot as $key => $value)
		{  
			if (strpos($spider, $key)!== false)
			{
				return $value;
			}
		}
		
		return false;
	}
	
	public static function isRobot()
	{
		if(self::getRobot()!==false)
		{
			return true;
		}
		return false;
	}
	
	public static function getMethod(){
		return $_SERVER['REQUEST_METHOD'];
	}
	
	public static function isPost(){
		return $_SERVER['REQUEST_METHOD']==='POST';
	}
	
	public static function request($name,$default=null){
		if(
			(isset($_GET[$name]))
			|| (isset($_POST[$name]))
		)
		{
			$value = isset($_GET[$name])?$_GET[$name]:$_POST[$name];
			if (is_string($value)) 
				return addslashes(trim($value));
			else 
				return $value;
		}
		else
			return $default;
	}
	
	public static function get($name,$default=null){
		if(isset($_GET[$name]))
		{
			if (is_string($_GET[$name])) 
				return addslashes(trim($_GET[$name]));
			else 
				return $_GET[$name];
		}
		else
			return $default;
	}
	public static function post($name,$default=null){
		if(isset($_POST[$name]))
		{
			if (is_string($_POST[$name])) 
				return addslashes(trim($_POST[$name]));
			else 
				return $_POST[$name];
		}
		else
			return $default;
	}

	public static function isAjax(){
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
            return true;
        }
        return false;
    }

	public static function getController($default = null){
		$paths = self::getPaths();
		if(isset($paths[0]))
			return $paths[0];
		else
			return $default;
	}
	public static function getAction(){
		$paths = self::getPaths();
		if(isset($paths[1]))
			return $paths[1];
		else
			return false;
	}
	public static function getRefererUrl(){
		if(self::$_refererUrl === null){
			if(isset($_SERVER['HTTP_REFERER'])){
				self::$_refererUrl = $_SERVER['HTTP_REFERER'];
			}
			else
			{
				self::$_refererUrl = self::getScriptUrl();
			}
		}
		return self::$_refererUrl;
	}
	public static function getRequestUrl(){
		if(self::$_requestUrl === null){
			self::$_requestUrl = $_SERVER ['REQUEST_URI'];
			$qpos = strpos ( self::$_requestUrl, "?" );
			if ($qpos > - 1)
				self::$_requestUrl = substr ( self::$_requestUrl, 0, $qpos );
		}
		return self::$_requestUrl;
	}
	public static function getPaths(){
		if(self::$_paths === null){
			$scripturl = self::getScriptUrl ();
			if (self::$showScriptName === false) {
				$scripturl = substr ( $scripturl, 0, strrpos($scripturl,'/'));
			}
			$pathstr = trim(substr ( self::getRequestUrl (), strlen ( $scripturl ) ),'/');
			//如果有后缀的地址，就去掉后缀
			$pos = strpos($pathstr,'.');
			if($pos>-1){
			    self::$suffix = substr($pathstr, $pos+1);
				$pathstr = substr($pathstr,0,$pos);
			}
			self::$_paths = array ();
			if ($pathstr) {
				$arr = explode ( '/', $pathstr );
				foreach ( $arr as $key => $value ) {
					self::$_paths [$key] = urldecode ( $value );
				}
			}
		}
		return self::$_paths;
	}
	public static function getScriptUrl()
	{
		if(self::$_scriptUrl===null)
		{
			$scriptName=basename($_SERVER['SCRIPT_FILENAME']);
			if(basename($_SERVER['SCRIPT_NAME'])===$scriptName)
				self::$_scriptUrl=$_SERVER['SCRIPT_NAME'];
			elseif(basename($_SERVER['PHP_SELF'])===$scriptName)
				self::$_scriptUrl=$_SERVER['PHP_SELF'];
			elseif(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$scriptName)
				self::$_scriptUrl=$_SERVER['ORIG_SCRIPT_NAME'];
			elseif(($pos=strpos($_SERVER['PHP_SELF'],'/'.$scriptName))!==false)
				self::$_scriptUrl=substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
			elseif(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT'])===0)
				self::$_scriptUrl=str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
			else
				throw new MException('MRoute Error to Get ScriptUrl');
		}
		return self::$_scriptUrl;
	}
	
	public static function getHostInfo()
	{
		$http = 'http';
		if(isset($_SERVER['HTTPS'])){
			$http = 'https';
		}
		if(self::$_hostInfo === null){
			if(isset($_SERVER['HTTP_HOST']))
				self::$_hostInfo=$http.'://'.$_SERVER['HTTP_HOST'];
			else
			{
				self::$_hostInfo=$http.'://'.$_SERVER['SERVER_NAME'];
			}
		}
		return self::$_hostInfo;
	}
	
	public static function getBaseUrl()
	{
		if(self::$_baseUrl === null){
			self::$_baseUrl = self::getHostInfo().self::getScriptUrl();
		}
		if(self::$showScriptName === false){
			return substr(self::$_baseUrl, 0,strlen(self::$_baseUrl)-10);
		}
	}
	
	public static function getUrl($localpath){
		return self::getBaseUrl().'/'.trim($localpath,'/');
	}
	
	public static function getBasePath(){
		if(self::$_basePath === null){
			self::$_basePath = $_SERVER['SCRIPT_FILENAME'];
			$pos = strrpos(self::$_basePath,'/');
			self::$_basePath = substr(self::$_basePath,0,$pos);
			if(self::$_basePath){
			    self::$_basePath = rtrim(self::$_basePath,'/').'/';
            }
		}
		return self::$_basePath;
	}

    public static function getIP() {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        }
        elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');

        }
        elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}