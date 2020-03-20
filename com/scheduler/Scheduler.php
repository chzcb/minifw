<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/11/23
 * Time: 11:11
 */

class Scheduler {

    public $tasks = array();


    private function _addTask($callback,$args,$type,$time,$delay){

    }

    /**
     * 增加延迟
     * @param $callback
     * @param $args
     * @param $delay
     */
    public function callLater($callback,$args,$delay){

    }

    /**
     * 固定间隔时间（毫秒）执行
     * @param $callback
     * @param $args
     * @param $interval
     * @param int $delay
     */
    public function callInterval($callback,$args,$interval,$delay=0){

    }

    /**
     * 固定频率（毫秒）执行
     * @param $callback
     * @param $args
     * @param $rate_time
     * @param int $delay
     */
    public function callRate($callback,$args,$rate_time,$delay=0){

    }

    public function start(){
        while(true){

            usleep(1000);
        }
    }
}