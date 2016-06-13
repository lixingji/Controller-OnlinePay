<?php

/**
 * Created by PhpStorm.
 * User: 37
 * Date: 2016/4/11
 * Time: 12:14
 */
namespace Alipay\Controller;

require_once(dirname(dirname(__FILE__)) . '/lib/alipay_submit.class.php');
use Admin\Common\Constant;
use Think\Controller;
use Doctors;
use Think\Model;

class APIController extends PayBaseController
{
    public function __construct()
    {
        parent::requestCheck(IS_POST, true);
    }

    /**
     * 药企端医药宝充值接口
     * 调用Alipay的api进行支付
     * User: 黎兴济
     * Date: 2016/4/11
     * Time: 12:14
     */
    function medicinePayApi()
    {
        //导入非class类的配置变量信息
        require_once(dirname(dirname(__FILE__)) . '/lib/config.php');

        //支付类型  即时到账
        $payment_type = "1";
        //服务器异步通知页面路径/index.php/Alipay/API/medicinePayApi
        $notify_url = Constant::URL . "/index.php/Alipay/NotifyUrl/medicineNotify";
        //页面跳转同步通知页面路径
        $return_url = Constant::URL . "/index.php/Alipay/ReturnUrl/medicineReturn";
        //商品展示地址
        $show_url = Constant::URL . "/index.php/User/EpCenter/charge";
        //商户订单号
        $out_trade_no = $_POST['WIDout_trade_no'];
        //付款金额
        $total_fee = $_POST['WIDtotal_fee'];
        //获取用户id
        $uid = $_SESSION['ADMIN_ID'];
        //获取用户id
//        $user_id = $_SESSION['user']['id'];

        //订单名称
        $subject = '药企医药宝充值';

        //订单描述
        $body = '充值进行学习晒票陈列任务的发布';

        //公司
        $company = '37度信息科技';
        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数

        //客户端的IP地址
        $exter_invoke_ip = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : ($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
        //非局域网的外网IP地址，如：221.0.0.1


        //校验表单，只允许传数字，防止注入其他字符
        $checkStr = "" . $out_trade_no . $total_fee;
        if (!preg_match("/^[0-9.C]+$/", $checkStr)) {
            echo "存在sql注入风险，请核对后再提交！";
            exit();
        }
        $create_time = date('Y-m-d h:i:s', time());
        //创建一条充值记录
        $chargeData = array('user_id'=>$uid , 'order_id'=>"$out_trade_no",'money'=>$total_fee,'create_time'=>"$create_time",'status'=>0,'pay_way'=>0);
        $chargeRst = M()->table("rf_ep_chargeRecord")->add($chargeData);
        //操作
        if (!$chargeRst) {
            echo "充值订单提交失败，请返回重试<script>setTimeout(function(){location.href=$show_url},2000)</script>";
            exit();
        }
        //插入交易管理监控sp_trade_manage表
       /* $manageData = array('user_id'=>$uid , 'out_trade_no'=>"$out_trade_no",'money'=>$total_fee,'time'=>"$create_time",'pay_way'=>0,'trade_content'=>"$subject",'type'=>2);
        $manageRst = M("tradeManage")->add($manageData);
        if (!$manageRst) {
            echo "充值订单提交失败，请返回重试<script>setTimeout(function(){location.href=$show_url},2000)</script>";
            exit();
        }*/

        /************************************************************/
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($alipay_config['partner']),
            "seller_email" => trim($alipay_config['seller_email']),
            "payment_type" => $payment_type,
            "notify_url" => $notify_url,
            "return_url" => $return_url,
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "show_url" => $show_url,
            "anti_phishing_key" => $anti_phishing_key,
            "exter_invoke_ip" => $exter_invoke_ip,
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
        );
        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认支付");
        echo json_encode($html_text);

    }

}