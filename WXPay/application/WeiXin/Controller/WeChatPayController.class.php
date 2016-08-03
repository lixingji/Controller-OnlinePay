<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/19 0019
 * Time: 上午 11:00
 */

namespace WeiXin\Controller;

use WeiXin\WeChat\WeChatPay;
use WeiXin\WeChat\HttpClient;
use WeiXin\lib\WxPayApi;
use Think\Controller;

class WeChatPayController extends Controller
{

	/**
	 * 微信支付
	 * @param orderNo
	 * @author 兴济
	 * @since  2016-01-19
	 */
	public function weChatPay()
	{
		$money = 1;//支付金额。
		$orderNo = "123333";//订单号  商户自己的订单号

		$params = array('appid' => WeChatPay::app_id , 'body' => "37度C信息服务" , 'mch_id' => WeChatPay::partner_id , 'nonce_str' => uniqid() , 'notify_url' => WeChatPay::notify_url , 'out_trade_no' => $orderNo , 'spbill_create_ip' => get_client_ip() , 'total_fee' => $money , 'trade_type' => "APP");
		$params['sign'] = WeChatPay::MakeSign($params);
		$xml = WeChatPay::ToXml($params);
		//向微信发送请求
		$result = HttpClient::quickPost(WeChatPay::prepay_url , $xml);
		$xml = WeChatPay::xml_to_array($result);
		if ($xml['prepay_id']) {
			$msg['data'] = array('prepayId' => $xml['prepay_id'] , 'package' => "Sign=WXPay" , 'partnerId' => WeChatPay::partner_id , 'timeStamp' => time() , 'nonceStr' => $xml['nonce_str'] , 'appId' => WeChatPay::app_id);
			$signData = array('prepayid' => $xml['prepay_id'] , 'package' => "Sign=WXPay" , 'partnerid' => WeChatPay::partner_id , 'timestamp' => time() , 'noncestr' => $xml['nonce_str'] , 'appid' => WeChatPay::app_id);
			$sign = WeChatPay::MakeSign($signData);
			$msg['data']['sign'] = $sign;
			$msg['result'] = array('state' => 0 , "description" => "获取成功");
		}
		else {
			$msg['data'] = array();
			$msg['result'] = array('state' => -1 , "description" => $xml['err_code_des']);
		}

		echo json_encode($msg);
		return;
	}


	/**
	 * 微信回调接口
	 * @author 兴济
	 * @since  2016-01-19
	 */
	public function notifyPayResult()
	{
		//获取通知的数据
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$notify = WeChatPay::xml_to_array($xml);
		$result = WxPayApi::notify($notify);
		$data = array();
		if ($result == true) {
			$orderId = $notify['out_trade_no'];
			$transaction_id = $notify['transaction_id'];
			$totalMoney = $notify['total_fee'];//交易费用 单位（分）

			//在下面修改订单状态
			$url = WeChatPay::order_paid_url . "&logId=" . $orderId;
			$orderResult=M("")->save(array());//修改商户自己的订单

			//订单状态修改成功
			if ($orderResult) {
				$data = array('return_code' => 'SUCCESS' , 'return_msg' => 'OK');
			}
			else {
				$data = array('return_code' => 'FAIL' , 'return_msg' => '报文为空');
			}
		}
		$result = WeChatPay::ToXml($data);
		echo $result;
		return;
	}

}