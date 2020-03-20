<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/11/14
 * Time: 14:23
 */

define('PHPEXCEL_ROOT',FW_PATH.'/libs/Classes/');

Fw::addAutoLoad(function($pClassName){
    if ((class_exists($pClassName, false)) || (strpos($pClassName, 'PHPExcel') !== 0)) {
        // Either already loaded, or not a PHPExcel class request
        return false;
    }
    $pClassFilePath = PHPEXCEL_ROOT .
        str_replace('_', DIRECTORY_SEPARATOR, $pClassName) .
        '.php';
    if ((file_exists($pClassFilePath) === false) || (is_readable($pClassFilePath) === false)) {
        // Can't load
        return false;
    }
    require($pClassFilePath);
});

PHPExcel_Shared_String::buildCharacterSets();

class ExcelUtils {

    static function _loadExcel($file){
        $reader = new PHPExcel_Reader_Excel2007;
        $starttime = Fw::getSysTime();
        Fw::log("加载$file");
        $objExcel = $reader->load($file);
        $endtime = Fw::getSysTime();
        Fw::log("cost:".($endtime-$starttime));
        return $objExcel;
    }

}