<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/11/14
 * Time: 17:54
 */

class DateUtils{

    public static function getSysTime()
    {
        $time = microtime();
        $arr = explode(" ", $time);
        return (double)($arr[0] + $arr[1]);
    }

    /**
     * 获得当前日期，格式为YYYYMMDD
     */
    public static function currentIntDate(){
        return intval(date('Ymd',time()));
    }

    /**
     * 获得本周周一的日期，格式YYYYMMDD
     */
    public static function currentIntWeekDate(){
        return intval(date("Ymd",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))));
    }

    /**
     * 获得上周周一的日期，格式YYYYMMDD
     * @return int
     */
    public static function lastIntWeekDate(){
        return intval(date("Ymd",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y"))));
    }

    /**
     * 获得本月1号的日期，格式YYYYMMDD
     * @return int
     */
    public static function currentIntMonthDate(){
        return intval(date("Ymd",mktime(0, 0 , 0,date("m"),1,date("Y"))));
    }

    /**
     * 获得本月的日期，格式YYYYMM
     */
    public static function currentIntMonth(){
        return intval(date("Ym",mktime(0, 0 , 0,date("m"),1,date("Y"))));
    }

    /**
     * 获得本月1号的日期，格式YYYYMMDD
     * @return int
     */
    public static function lastIntMonthDate(){
        return intval(date("Ymd",mktime(0, 0 , 0,date("m")-1,1,date("Y"))));
    }

    /**
     * 获得本月的日期，格式YYYYMM
     */
    public static function lastIntMonth(){
        return intval(date("Ym",mktime(0, 0 , 0,date("m")-1,1,date("Y"))));
    }

    /**
     * 获得下月1号的日期，格式YYYYMMDD
     * @return int
     */
    public static function nextIntMonthDate(){
        return intval(date("Ymd",mktime(0, 0 , 0,date("m")+1,1,date("Y"))));
    }

    /**
     * 获得下月的日期，格式YYYYMM
     */
    public static function nextIntMonth(){
        return intval(date("Ym",mktime(0, 0 , 0,date("m")+1,1,date("Y"))));
    }

    /**
     * 获得本季度1号的日期，格式YYYYMMDD
     * @return int
     */
    public static function currentIntQuarterDate(){
        $quarterIndex = ceil((date('n'))/3);//当月是第几季度
        return intval(date('Ymd', mktime(0, 0, 0,$quarterIndex*3-2,1,date('Y'))));
    }

    /**
     * 获得本季度的日期，格式YYYYMM
     */
    public static function currentIntQuarter(){
        $quarterIndex = ceil((date('n'))/3);//当月是第几季度
        return intval(date('Ym', mktime(0, 0, 0,$quarterIndex*3-2,1,date('Y'))));
    }

    /**
     * 获得上季度1号的日期，格式YYYYMMDD
     * @return int
     */
    public static function lastIntQuarterDate(){
        $quarterIndex = ceil((date('n'))/3);//当月是第几季度
        return intval(date('Ymd', mktime(0, 0, 0,$quarterIndex*3-5,1,date('Y'))));
    }

    /**
     * 获得上季度的日期，格式YYYYMM
     */
    public static function lastIntQuarter(){
        $quarterIndex = ceil((date('n'))/3);//当月是第几季度
        return intval(date('Ym', mktime(0, 0, 0,$quarterIndex*3-5,1,date('Y'))));
    }

    /**
     * 获得下季度1号的日期，格式YYYYMMDD
     * @return int
     */
    public static function nextIntQuarterDate(){
        $quarterIndex = ceil((date('n'))/3);//当月是第几季度
        return intval(date('Ymd', mktime(0, 0, 0,$quarterIndex*3+1,1,date('Y'))));
    }

    /**
     * 获得下季度的日期，格式YYYYMM
     */
    public static function nextIntQuarter(){
        $quarterIndex = ceil((date('n'))/3);//当月是第几季度
        return intval(date('Ym', mktime(0, 0, 0,$quarterIndex*3+1,1,date('Y'))));
    }

    /**
     * 获得当前时间 HHIISS
     * @return int
     */
    public static function currentIntTime(){
        return intval(date('His',time()));
    }

    /**
     * 通过strtotime获得YYYYMMDD的日期
     * @param $format
     * @return int
     */
    public static function getIntDate($datestr){
        return intval(date('Ymd',strtotime($datestr)));
    }

    /**
     * 通过YYYYMMDD的格式，获得格式化后的日期
     * @param  $format +1 -1
     * @param $intdate
     */
    public static function getDatefromIntDate($format, $intdate){
        $year = floor($intdate/10000);
        $month = floor(($intdate%10000)/100);
        $day = $intdate%100;
        return date($format,mktime(0,0,0,$month,$day,$year));
    }

    /**
     * 通过年和第几周 获得周一的日期
     * @param $year
     * @param $week
     * @param $format
     * @return false|string
     */
    public static function getDatefromWeek($year,$week,$format){
        $time = mktime(0,0,0,1,1,$year);
        $weekindex = date('w',$time);
        return date($format,$time+86400*7*$week-$weekindex*86400);
    }
}