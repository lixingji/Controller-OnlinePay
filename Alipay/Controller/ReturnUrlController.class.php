<?php

/**
 * 支付回调页面
 * User: 黎兴济
 * Date: 2016/4/11
 * Time: 12:14
 */
namespace Alipay\Controller;

require_once(dirname(dirname(__FILE__)) . '/lib/alipay_notify.class.php');
use Admin\Common\Constant;
use Think\Controller;
use Doctors;
use Think\Model;

class ReturnUrlController extends Controller
{

    /**
     * 医药宝回调处理页面
     * User: 黎兴济
     * Date: 2016/4/11
     * Time: 12:14
     */
    function medicineReturn()
    {
        //导入非class类的配置变量信息
        require_once(dirname(dirname(__FILE__)) . '/lib/config.php');
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        if ($verify_result) {//验证成功
            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];

            //支付宝交易号
            $trade_no = $_GET['trade_no'];

            //交易状态
            $trade_status = $_GET['trade_status'];

            //获取用户id
            $uid = $_SESSION['ADMIN_ID'];
            //获取用户id
//            $user_id = $_SESSION['user']['id'];
            //页面跳转
            $show_url = Constant::URL . "/index.php/Admin/EpCenter/recharge";
            echo "<script>setTimeout(function(){ location.href='" . $show_url . "'},3000);</script>";

            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
//
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                //判断是否已经更新记录字段，防止页面重刷
                $chargeRst = M()->table("rf_ep_chargeRecord")->field('status,money')->where("order_id='$out_trade_no'")->find();
                if ($chargeRst['status'] == 1) {
                    echo "请勿重刷页面！";
                    exit();
                }
                $queryChargeMoney = $chargeRst['money'];//充值金额

                //更新充值状态
                $data1 = array('status' => 1);
                $updateChargeRst = M()->table("rf_ep_chargeRecord")->where("order_id='$out_trade_no'")->save($data1);
                if (!$updateChargeRst) {
                    echo "充值失败";
                    exit();
                }

                //更新交易管理监控数据表
                /*$data2 = array('status' => 1, 'trade_no' => "$trade_no");
                $updateManageRst = M()->table("sp_trade_manage")->where("out_trade_no='$out_trade_no'")->save($data2);
                if (!$updateManageRst) {
                    echo "更新交易订单失败！";
                    exit();
                }*/

                //查询用户剩的钱和充值的钱
                $walletChargeRst = M()->table("rf_wallet_medicine")->field('money')->where("user_id=$uid")->find();
                //提取数据
                if (!$walletChargeRst) {//判断以往是否有过充值记录
                    $data3 = array('user_id' => $uid, 'money' => $queryChargeMoney);
                    M()->table("rf_wallet_medicine")->add($data3);
                } else {
                    $totalMoney = $walletChargeRst['money'] + $queryChargeMoney;
                    //更新钱余额
                    $walletmoney['money'] = $totalMoney;
                    $wmRst = M('wallet_medicine')->where("user_id=$uid")->save($walletmoney);
                    if (!$wmRst) {
                        echo "更新钱包失败";
                        exit();
                    }
                }

                echo "<h1 style='color:green;margin:30px; '>37service提醒您：本次充值成功！</h1><h2 style='margin:30px; '>页面跳转中....</h2>";

            } else {
                /**
                 * 充值失败，修改充值状态
                 */
                $data1 = array('status' => 2);
                $updateChargeRst = M()->table("rf_ep_chargeRecord")->where("order_id='$out_trade_no'")->save($data1);
                if (!$updateChargeRst) {
                    echo "更新充值失败";
                    exit();
                }
                echo "<h1 style='color:green; '>充值失败</h1><br />";
                echo "<br/><br/> <h2>页面跳转中....</h2>";
            }
            exit;
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            echo "验证失败";
            exit;
        }

    }

}