<?php
namespace WeiXin\lib;

use WeiXin\WeChat\WeChat;
use WeiXin\WeChat\WeChatPay;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/19 0019
 * Time: 下午 4:48
 */
class WxPayApi{

    /**
     *
     * 支付结果通用通知
     * @param function $callback
     * 直接回调函数使用方法: notify(you_function);
     * 回调类成员函数方法:notify(array($this, you_function));
     * $callback  原型为：function function_name($data){}
     */
    public static function notify($data)
    {
        //如果返回成功则验证签名
        try {
            $result = WxPayApi::CheckSign($data);
        } catch (WxPayException $e){
            $e->errorMessage();
            return false;
        }
        return $result;
    }

    /**
     *
     * 检测签名
     */
    public function CheckSign($data)
    {
        //fix异常
        if(!WxPayApi::IsSignSet($data)){
            return false;
        }

        $sign = WeChatPay::MakeSign($data);//微信支付签名
        $weChatSign = WeChat::MakeSign($data);//公众号签名
        if(WxPayApi::GetSign($data) == $sign || $weChatSign == WxPayApi::GetSign($data)){
            return true;
        }
        return false;
    }
    /**
     * 判断签名，详见签名生成算法是否存在
     * @return true 或 false
     **/
    public function IsSignSet($data)
    {
        return array_key_exists('sign', $data);
    }

    /**
     * 获取签名，详见签名生成算法的值
     * @return 值
     **/
    public function GetSign($data)
    {
        return $data['sign'];
    }

    /**
     *
     * notify回调方法，该方法中需要赋值需要输出的参数,不可重写
     * @param array $data
     * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
     */
    final public function NotifyCallBack($data)
    {
        $msg = "OK";
        $result = $this->NotifyProcess($data, $msg);

        if($result == true){
            $this->SetReturn_code("SUCCESS");
            $this->SetReturn_msg("OK");
        } else {
            $this->SetReturn_code("FAIL");
            $this->SetReturn_msg($msg);
        }
        return $result;
    }
}