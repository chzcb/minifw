<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/11/22
 * Time: 19:16
 */
class ArrayUtils
{
    public static function to_object($array){
        $obj = new class{};
        foreach ($array as $key=>&$value) {
            if(is_array($value))
                $obj[$key] = self::to_object($value);
            else
                $obj[$key] = $value;
        }
        return $obj;
    }

    public static function pick($arr,...$keys){
        $newarr = array();
        foreach ($keys as $key){
            if(isset($arr[$key])){
                $newarr[$key] = $arr[$key];
            }
            else
            {
                $newarr[$key] = null;
            }
        }
        return $newarr;
    }

    public static function omit(&$arr,...$keys){
        foreach ($keys as $key){
            if(isset($arr[$key])){
                unset($arr[$key]);
            }
        }
        return $arr;
    }

    public static function isEqual($arr1,$arr2){
        if(count($arr1) != count($arr2)){
            return false;
        }
        foreach ($arr1 as $key=>$item1) {
            if(!isset($arr2[$key])){
                return false;
            }
            if($item1 != $arr2[$key]){
                return false;
            }
        }
        return true;
    }

    public static function pickByArray($arr,$keys){
        $newarr = array();
        foreach ($keys as $key){
            $newarr[$key] = $arr[$key];
        }
        return $newarr;
    }

    public static function pickAndChange($arr,$keysMap){
        $newarr = array();
        foreach ($keysMap as $key=>$newKey){
            if(isset($arr[$key]))
                $newarr[$newKey] = &$arr[$key];
            else
                $newarr[$newKey] = null;
        }
        return $newarr;
    }

    public static function each($arr,$callback){
        foreach ($arr as &$item) {
            $item = call_user_func_array($callback,array($item));
        }
        return $arr;
    }

    public static function defaults($arr,$defaults){
        foreach ($defaults as $key=>$value){
            if(!isset($arr[$key])){
                $arr[$key] = $value;
            }
        }
        return $arr;
    }

    public static function every($arr,$callback){
        foreach ($arr as &$item) {
            if(call_user_func_array($callback,array($item)) !== false){
                return true;
            }
        }
        return false;
    }

    public static function extend(&$arr,...$args){
        foreach ($args as $item) {
            if(is_array($item) || is_object($item)){
                foreach ($item as $key=>$value){
                    $arr[$key] = $value;
                }
            }
        }
        return $arr;
    }


    public static function keyAndValue($keys,$values){
        if(empty($keys))
            return $values;
        $arr = array();
        foreach ($keys as $index=>$key){
            $arr[$key] = isset($values[$index])?$values[$index]:null;
        }
        return $arr;
    }

    public static function repeat($item,$repCount){
        $arr = array($repCount);
        for($i=0;$i<$repCount;$i++){
            $arr[] = $item;
        }
        return $arr;
    }

    public static function values($array,...$keys){
        $ret = array();
        foreach($keys as $key){
            $ret[] = isset($array[$key])?$array[$key]:null;
        }
        return $ret;
    }
}