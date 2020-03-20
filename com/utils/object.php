<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/11/22
 * Time: 20:08
 */

class ObjectUtils
{
    public static function to_array($obj){
        $array = array();
        foreach ($obj as $key => $value){
            $type = gettype($value);
            if($type == "object" || $type == "array"){
                $array[$key] = self::to_array($value);
            }
            else
                $array[$key] = $value;
        }
        return $array;
    }
}