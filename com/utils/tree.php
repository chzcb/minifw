<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2018/1/7
 * Time: 19:07
 */

define('UNDEFINED_MENU_KEY','__undefined__');

class TreeUtils
{
    public static function mkTree($list,$key,$parentKey,$childName='children'){
        $root = array();
        $root[$key] = UNDEFINED_MENU_KEY;
        $root[$childName] = array();
        foreach ($list as &$node) {
            if(!self::addNode($root,$node,$key,$parentKey,$childName)){
                array_push($root[$childName],$node);
            }
        }
        return $root;
    }

    private static function addNode(&$parent,$node,$key,$parentKey,$childName='children'){
        //如果node是root的子，那么久加入到root的里面
        if($parent[$key]."" == $node[$parentKey].""){
            if(!isset($parent[$childName])){
                $parent[$childName] = array();
            }
            array_push($parent[$childName],$node);
            return true;
        }
        if(isset($parent[$childName])){
            foreach ($parent[$childName] as &$subitem) {
                if(self::addNode($subitem,$node,$key,$parentKey,$childName)){
                    return true;
                }
            }
        }
        return false;
    }
}