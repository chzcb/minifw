<?php
/**
 * Created by PhpStorm.
 * User: zhangcb
 * Date: 2018/11/7
 * Time: 15:41
 */

require_once 'smtp.php';

class MailUtils
{
    static $defaultConfig = array(
        "smtp_server"=>"smtp.mxhichina.com",
        "smtp_port"=>465,
        "sendmail_from"=>"support@gsoms.com",
        "sendmail_pwd"=>"Gsdev@2018"
    );

    /**
     *
     * data {mail_to,title,content}
     *
     * @param $data
     * @return bool
     */
    public static function sendMail($data) {
        $params = array();
        ArrayUtils::extend($params,self::$defaultConfig,$data);
        // 邮件格式 （HTML/TXT）
        $mailType="HTML";
        // true表示是否身份验证
        $smtp=new Smtp();
        $smtp->setServer($params['smtp_server'],$params['sendmail_from'],$params['sendmail_pwd'],$params['smtp_port'],true);
        $smtp->setFrom($params['sendmail_from']);
        if(is_array($params['mail_to'])) {
            foreach ($params['mail_to'] as $to) {
                $smtp->setReceiver($to);
            }
        } else if(strstr($params['mail_to'],',')){
            $arr = explode(",",$params['mail_to']);
            foreach ($arr as $to) {
                $smtp->setReceiver($to);
            }
        } else {
            $smtp->setReceiver($params['mail_to']);
        }

        $smtp->setMail($params['title'],$params['content']);
        return $smtp->sendMail();
    }
}