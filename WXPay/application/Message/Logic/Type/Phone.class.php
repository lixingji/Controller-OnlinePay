<?php

/*
 * 
 * PHP version 5.5
 *
 * @copyright  Copyright (c) 2012-2015
 * @link       http://www.37service.com
 * @license    广州37度网络科技版权所有
 */
namespace Message\Logic\Type;

class Phone
{

    /**
     * 短信平台类型
     * @var unknown
     */
    public static $sendMethods = array('duanxinbao');

    /**
     * 短信模板
     * @var unknown
     */
    public static $tplList = array(
        101 => array(
            'title' => '用户', 
            'data' => array(
              /* 验证码 */
                0 => array(1 => array('content' => '你本次的手机验证码为：{{code}},有效期30分钟。')),
               /* 重置 密码 */
                1 => array(1 => array('content' => '密码重置成功，密码为：{{newPassword}}。请尽快登录更改。')))

            ));

    /**
     * 发送
     * 
     * 
     * @param string $reciver  手机号
     * @param string $tplContent 短信内容
     * @param int $uid  会员id
     * @author 张涛
     * @since  2015年7月19日
     */
    public static function send($reciver, $tplContent, $uid)
    {
        //调用短信接口发送短信
        $methods = self::$sendMethods;
        $rand = mt_rand(0, count($methods) - 1);
        return self::$methods[$rand]($reciver, $tplContent, $uid);
    }

    /**
     * 短信宝平台
     * 
     * 
     * @param string $tel
     * @param string $content
     * @param int $uid
     * @return array
     * @author 张涛
     * @since  2015年7月20日
     */
    public static function duanxinbao($tel, $content, $uid)
    {
        $config = C('SMS.DUANXINBAO');
        $sendurl = $config['URL'] . "sms?u=" . $config['USER'] . "&p=" . md5($config['PASSWORD']) . "&m=" . $tel . "&c=" .
             urlencode($content['content']);
        $result = file_get_contents($sendurl);
        if ($result == '0') {
            $return = array('status' => 1, 'info' => L('SEND_SUCCESS'));
        } else {
            $return = array('status' => 0, 'info' => L('SEND_FAIL'));
        }
        $ip=GetIP($return);
        //保存发送记录
        $sendLog = array(
            'uid' => $uid,
            'phone' => $tel,
            'verify_code' => $content['verifyCode'],
            'content' => $content['content'],
            'type' => '1',
            'status' => $result,
            'info' => $config['STATUS_INFO'][$result],
            'time' => gmtime(),
            'ip' => "$ip");
        D('Message/SmssendLog')->saveSendLog($sendLog);
        return $return;
    }
}
?>