<?php
/**
 * Created by PhpStorm.
 * User: 陈旭生
 * Date: 15-8-5
 * Time: 上午10:41
 */

namespace Common\Controller;
use Think\Cache\Driver\Redis;
use Think\Controller;

class TokenController extends Controller{

    public function __construct(){}

    private $expireTime=30*60;//token过期时间为30分钟


    /**
     * 判断token是否超时  超时删除token
     * @author 黎兴济
     * @since  2016-06-15
     */
    public function isTokenExpire(){

    }

    /**
     * 每次正常请求就更新token的时间
     * @author 黎兴济
     * @since  2016-06-15
     */
    public function updateToken(){

    }
    /**
     * 生成新的token
     * $imei 手机串号
     * $id 用户id
     *return boolean
     *
     *
     * @author 黎兴济
     * @since  2016-6-15
     */
    public function getToken($imei,$id){

        $data = array(
            'imei'=>$imei,
            'id' => $id,
            'session_id'=>$_SESSION['session_id'],
            'login_time'=>time()
        );
        $da=json_encode($data);
        $crypt = new \Common\Aes\Aes();
        $token =  $crypt->encode($da);
        //用session保存  $_SESSION['token']
        return $token;
         /*$data=array(
             'token'=>$token,
         );
        $res = M('users')->where("id = $id")->setField($data);
        if($res){
            return 1;
        }else{
            return false;
        }*/
    }

    /**
     * 获取用户id
     *$token string
     * return $id or false
     *
     * @author 黎兴济
     * @since  2016-6-15
     */
    public function getUserId($token){
       $crypt = new \Common\Aes\Aes();
         $str =  $crypt->decode($token);
         $str=json_decode($str,true);
         $id=$str['id'];
        return $id;
    }
    /**
     * 获取用户imei
     * $token string
     * return $imei or false
     *
     * @author 黎兴济
     * @since  2016-6-15
     */
    public function getImei($token){
       $crypt = new \Common\Aes\Aes();
         $str =  $crypt->decode($token);
         $str=json_decode($str,true);
         $imei=$str['imei'];
        return $imei;
    }
}