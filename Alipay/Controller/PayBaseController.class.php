<?php

/**
 * 调用支付宝支付基础类
 * User: 黎兴济
 * Date: 2016/4/11
 * Time: 12:14
 */
namespace Alipay\Controller;

use Think\Controller;
use Think\Model;

class PayBaseController extends Controller
{
    public function __construct()
    {
//        requestCheck(IS_POST);
    }

    public function requestCheck($requestMothed,$isLogin=false )
    {
        if (IS_POST == $requestMothed) {
            if(empty($_POST) ){
                $msg['result'] = array('state' => -4, "description" => "无请求数据");
                echo json_encode($msg);
                exit;
            }
            //网页端无token
            /*if($isLogin){
                $formDatas = array(
                    // 处理post数据
                    'token' => I('post.token', ''),
                    'imei' => I('post.imei', ''));
                extract($formDatas);

                $res = R('Doctors/Login/isLogin', $formDatas);
                if ($res == -1) {
                    $msg['result'] = array('state' => -1003, "description" => "请登录后操作");
                    echo json_encode($msg);
                    exit;
                }
                if ($res == 0) {
                    $msg['result'] = array('state' => -1003, "description" => "帐号已过期，请重新登录");
                    echo json_encode($msg);
                    exit;
                }
            }*/

        } else {
            $msg['result'] = array('state' => -3, "description" => "必须使用post请求");
            echo json_encode($msg);
            exit;
        }
    }

}