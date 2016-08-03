<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/19 0019
 * Time: 下午 12:02
 */

namespace WeiXin\WeChat;

//微信支付
class WeChatPay{
    const app_id = "wxb810cd6f978f1c78";// 公众号身份标识
    const app_key = "37Sservicecoom20160118yangbobruc";// 加密密钥 Key，也即appKey
    const partner_key = '37Sservicecoom20160118yangbobruc';// 财付通商户权限密钥 Key
    const partner_id = '1281041501';// 财付通商户身份标识
    const notify_url = "http://test.37service.com/index.php/Weixin/WeChatPay/notifyPayResult";// 微信支付完成服务器通知页面地址
    const prepay_url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    const order_paid_url = "http://mall.37service.com/mobile/flow.php?act=WeChatPay";//微信回调修改商城订单状态地址
    //const app_secret = "852685edbd2f0d893e237dd150e69692";// 权限获取所需密钥 Key  公众号用
    /* const order_paid_url = "http://mall.37service.com/mobile/flow.php?act=WeChatPay";//微信回调修改商城订单状态地址
    const query_order_url = "https://api.mch.weixin.qq.com/pay/orderquery";//订单查询链接
    const refund_order_url = "https://api.mch.weixin.qq.com/secapi/pay/refund";//申请退款链接
    const close_order_url = "https://api.mch.weixin.qq.com/pay/closeorder";//关闭订单接口
    const query_refund_order_url = "https://api.mch.weixin.qq.com/pay/refundquery";//查询退款链接
    const reverse_order_url = "https://api.mch.weixin.qq.com/secapi/pay/reverse";//撤销订单
    const SSLCERT_PATH = '/application/WeiXin/WeChat/apiclient_cert.pem';
    const SSLKEY_PATH = '/application/WeiXin/WeChat/apiclient_key.pem';
    const ROOT_CAPATH = '../cert/rootca.pem';
    const TRANSFERS_URL = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";//企业付款*/

    /**
     * 生成签名
     * @param $data
     * @return string
     * @since  2016-01-18
     */
    public static function MakeSign($data)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = WeChatPay::ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".WeChatPay::partner_key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     * @param $data
     * @return string
     * @since  2016-01-18
     */
    public function ToUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 转换xml
     * @param $data
     * @return string
     * @since  2016-01-18
     */
    public static function ToXml($data)
    {
        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            $xml.="<".$key.">".$val."</".$key.">";
           /* if (is_numeric($val)){
               // $xml.="<".$key.">".$val."</".$key.">";
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }*/

        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * @param 将xml转换成为数组
     * @return array
     * @since  2016-01-19
     */
    public static function xml_to_array($xml)
    {
        $postObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $array = json_decode(json_encode($postObj), true); // xml对象转数组
        return array_change_key_case($array, CASE_LOWER);
    }

}