<?php

class StringUtils
{
	private static $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
	public static function randomString($len){
		$ret = '';
		$size = strlen(self::$chars)-1;
		while($len-->0){
			$ret .= self::$chars[rand(0,$size)];
		}
		return $ret;
	}
	public static function stdin($name,$default=null,$pre="请输入"){
		echo $pre.$name.($default!==null?"[".$default."]":"").">";
		$fp = fopen('/dev/stdin', 'rb');

		$input = fgets($fp, 255);
		fclose($fp);
		if(strlen($input)>1){
			return chop($input);
		}
		else
		{
			if($default!==null){
				return $default;
			}
			return self::stdin($name,$default,$pre);
		}
	}
	public static function json_encode($input){
	        // 从 PHP 5.4.0 起, 增加了这个选项.
		if(defined('JSON_UNESCAPED_UNICODE')){
			return json_encode($input, JSON_UNESCAPED_UNICODE);
		}
		if(is_string($input)){
			$text = $input;
			$text = str_replace('\\', '\\\\', $text);
			$text = str_replace(
				array("\r", "\n", "\t", "\""),
				array('\r', '\n', '\t', '\\"'),
				$text);
			return '"' . $text . '"';
		}else if(is_array($input) || is_object($input)){
			$arr = array();
			$is_obj = is_object($input) || (array_keys($input) !== range(0, count($input) - 1));
			foreach($input as $k=>$v){
				if($is_obj){
					$arr[] = self::json_encode($k) . ':' . self::json_encode($v);
				}else{
					$arr[] = self::json_encode($v);
				}
			}
			if($is_obj){
				return '{' . join(',', $arr) . '}';
			}else{
				return '[' . join(',', $arr) . ']';
			}
		}else{
			return $input . '';
		}
	}


	public static function json_decode($input,$assoc = true){
	    if(strstr($input,"\\")){
	        $input = stripslashes($input);
        }
        if(preg_match('/\w:/', $input)){
            $input = preg_replace('/([,{])(\w+):/is', '$1"$2":', $input);
        }
        return json_decode($input, $assoc);

    }

    /**
     * 检查字符串是否以$needle开始的
     * @param $str
     * @param $needle
     * @return bool
     */
	public static function start_with($str,$needle){
	    return strpos($str,$needle)  === 0;
    }

    /**
     * 检查 $haystack 是否以$needle结尾的
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function end_with($haystack,$needle){
	    $len = strlen($needle);
	    if($len == 0)
	        return true;
	    return (substr($haystack,-$len) === $needle);
    }


    static $str1;
    static $str2;
    static $c = array();

    /**
     * 返回两个串的相似度
     */
	public static function get_similar($str1,$str2){
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        if($len1 == 0 || $len2 == 0){
            return 0;
        }
        $len = strlen(self::get_lcs($str1, $str2, $len1, $len2));
        return $len * 2 / ($len1 + $len2);
    }

    /**
     * 返回串一和串二的最长公共子序列
     */
    public static function get_lcs($str1, $str2, $len1 = 0, $len2 = 0) {
        self::$str1 = $str1;
        self::$str2 = $str2;
        if ($len1 == 0) $len1 = strlen($str1);
        if ($len2 == 0) $len2 = strlen($str2);
        self::initC($len1, $len2);
        return self::printLCS($len1 - 1, $len2 - 1);
    }
    
    static function initC($len1, $len2) {
        self::$c = array();
        for ($i = 0; $i < $len1; $i++) self::$c[$i][0] = 0;
        for ($j = 0; $j < $len2; $j++) self::$c[0][$j] = 0;
        for ($i = 1; $i < $len1; $i++) {
            for ($j = 1; $j < $len2; $j++) {
                if (self::$str1[$i] == self::$str2[$j]) {
                    self::$c[$i][$j] = self::$c[$i - 1][$j - 1] + 1;
                } else if (self::$c[$i - 1][$j] >= self::$c[$i][$j - 1]) {
                    self::$c[$i][$j] = self::$c[$i - 1][$j];
                } else {
                    self::$c[$i][$j] = self::$c[$i][$j - 1];
                }
            }
        }
    }
    static function printLCS($i, $j) {
        if ($i == 0 || $j == 0) {
            if (self::$str1[$i] == self::$str2[$j]) return self::$str2[$j];
            else return "";
        }
        if (self::$str1[$i] == self::$str2[$j]) {
            return self::printLCS($i - 1, $j - 1).self::$str2[$j];
        } else if (self::$c[$i - 1][$j] >= self::$c[$i][$j - 1]) {
            return self::printLCS($i - 1, $j);
        } else {
            return self::printLCS($i, $j - 1);
        }
    }

    public static function csv($csvStr,$split=',',$hasHeader=true){
        global $headers;
        $GLOBALS['split'] = $split;
        $rowlist = explode("\r\n",$csvStr);
        if($hasHeader){
            $headers = explode(',',trim(array_shift($rowlist)));
        } else {
            $headers = array();
        }

        return array('headers'=>$headers,'content'=>ArrayUtils::each($rowlist,function($item){
            global $split;
            return explode($split,trim($item));
        }));
    }

    public static function numberToString($num){
        if (stripos($num,'e')===false) return $num;
        $num = trim(preg_replace('/[=\'"]/','',$num,1),'"');//出现科学计数法，还原成字符串
        $result = "";
        while ($num > 0){
            $v = $num - floor($num / 10)*10;
            $num = floor($num / 10);
            $result   =   $v . $result;
        }
        return $result;
    }


}


