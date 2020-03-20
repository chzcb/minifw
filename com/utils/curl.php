<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2017/12/22
 * Time: 17:59
 */
class CurlUtils
{

    private static function checkArgs($arr,...$vars){
        foreach($vars as $var){
            if(!isset($arr[$var])){
                throw new MException("no arg ".$var);
            }
        }
        return true;
    }

    /**
     * opts 的 参数
     * header = array('Content-Type: application/json; charset=utf-8','Content-Length: 2')
     * url
     * referer
     *
     * @param $opts
     * @return mixed
     */
    public static function post($opts){

        self::checkArgs($opts,'url');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $opts['url']);
        if(isset($opts['referer']))
            curl_setopt($ch, CURLOPT_REFERER, $opts['referer']);
        else
            curl_setopt($ch, CURLOPT_REFERER, 1);
        if(strstr($opts['url'],'https')){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(isset($opts['header']))
            curl_setopt($ch, CURLOPT_HTTPHEADER, $opts['header']);
        if(isset($opts['data']))
            curl_setopt($ch, CURLOPT_POSTFIELDS,$opts['data']);


        curl_setopt($ch, CURLOPT_HEADER, true);

        $output = curl_exec($ch);
        curl_close($ch);
        $headers = explode("\r\n",$output);
        $body = array_pop($headers);

        //开始解析头
        foreach($headers as $header){
            if(strstr($header,'Content-Type:')){
                preg_match('/charset=(.*)/',$header,$arr);
                $body = mb_convert_encoding($body, 'UTF-8',$arr[1]);
            }
        }
        return $body;
    }

    public static function get($opts){
        if(is_string($opts)){
            $opts = array('url'=>$opts);
        } else {
            self::checkArgs($opts,'url');
        }

        $ch = curl_init();

        $url = $opts['url'];
        if(isset($opts['data'])){
            if(strstr($url,'?')){
                $url = $url.'&'.http_build_query($opts['data']);
            }
            else
            {
                $url = rtrim($url,'?').'?'.http_build_query($opts['data']);
            }
        }

        curl_setopt($ch,CURLOPT_URL,$url);
        if(isset($opts['referer']))
            curl_setopt($ch, CURLOPT_REFERER, $opts['referer']);

        curl_setopt($ch, CURLOPT_HEADER, true);


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(isset($opts['header']))
            curl_setopt($ch, CURLOPT_HTTPHEADER, $opts['header']);

        $output = curl_exec($ch);

        if(curl_getinfo($ch,CURLINFO_HTTP_CODE) == '200'){
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($output,0,$headerSize);
            $body = substr($output,$headerSize);
            $headers = explode("\r\n",$header);
            //开始解析头
            foreach($headers as $header) {
                if (strstr($header, 'Content-Encoding:') && preg_match('/Content-Encoding: (.*)/', $header, $arr)) {
                    if (trim($arr[1]) == 'gzip') {
                        $is_gzip = true;
                    }
                } else if (strstr($header, 'Content-Type:') && preg_match('/charset=(.*)/', $header, $arr)) {
                    $need_convert= $arr[1];
                }
            }
            //是否压缩
            if(isset($is_gzip) && $is_gzip){
                $body = gzdecode($body);
            }
            if(isset($need_convert) && $need_convert){
                $body = mb_convert_encoding($body, 'UTF-8', $need_convert);
            }

        } else {
            Fw::log("发生错误：".curl_getinfo($ch,CURLINFO_HTTP_CODE));
            $body = "";
        }
        curl_close($ch);

        return $body;
    }
}