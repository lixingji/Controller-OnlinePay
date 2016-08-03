<?php

/*
 * 
 * PHP version 5.5
 *
 * @copyright  Copyright (c) 2012-2015
 * @link       http://www.37service.com
 * @license    广州37度网络科技版权所有
 */
namespace Message\Model;
use AccessAuth\Controller\AccessCheckController;
use Think\Log;

/**
 * 短信日记记录模型
 * 
 * @author    张涛
 * @since     2015年7月19日
 * @version   1.0
 */
class SmssendLogModel extends \Think\Model
{
    private $whiteList = array("18819816211","15919873218","15889643139","15889605554","15888960555","18588648018","18688596873","15889643139");

    /**
     * 保存短信发送日志记录
     * 
     * 
     * @param array $logData
     * @author 张涛
     * @since  2015年7月19日
     */
    public function saveSendLog(array $logData)
    {
        $this->add($logData);
    }

    /**
     * 根据手机号获取最新手机验证码
     * 
     * 
     * @param string $phone
     * @author 张涛
     * @since  2015年7月20日
     */
    public function getLastVerifyCode($phone)
    {
        $sql = "SELECT verify_code,time FROM sp_smssend_log WHERE phone = '%s' AND status= %d AND  verify_code <> '' ORDER BY msg_id DESC LIMIT 1";
        $list = M('SmssendLog')->query(sprintf($sql, $phone, 0));
        return $list[0];
    }

    /**
     * 根据手机号将最新手机验证码置0
     *
     *
     * @param string $phone
     * @author 陈旭生
     * @since  2015年8月3日
     */
    public function deleteLastVerifyCode($phone){
        $res = M('SmssendLog')-> where("phone = $phone")->order('time desc')->limit(1)  -> setField('verify_code',0);
        return $res;
    }
    
    /**
     * 检测频繁发送验证码【暂时限定1min中只能获取一次】
     * 
     * 
     * @param unknown $phone
     * @param $sec 设置发送频率的时间限制 以秒为单位
     * @author 张涛
     * @since  2015年7月20日
     */
    public function sendSmsOften($phone , $sec='60'){
        $where = array('phone' => $phone, 'time' => array('gt', gmtime() - $sec),'status'=>0);
        $smsModle = M('SmssendLog') ;
        $isOk = false;
        $ip = get_client_ip();
        $time = (gmtime() - $sec*60);
        $is = in_array($phone,$this->whiteList);
        if($is){
            return false;
        }
       if(AccessCheckController::isInBlacklist($ip) || AccessCheckController::isInBlacklist($phone)) {
            //$this->hackMessage();
        }
        if ($smsModle->where($where)->find()) {
            $isOk = true;
            //$where = array('phone' => $phone, 'time' => array('gt', gmtime() - $sec*60*24), 'status' => 0);
            AccessCheckController::addToBlacklist($ip,$phone);
            $this->hackMessage();

        }else if($smsModle->where("(phone=$phone ) and time > $time")->count() > 12){
            $isOk = true;
            AccessCheckController::addToBlacklist($ip,$phone);
            $this->hackMessage();
        }
        //Log::record(" sql = ".$smsModle->getLastSql());
        return $isOk;
    }

    public function hackMessage(){
        die("found network attack, i will got u...");
    }
}