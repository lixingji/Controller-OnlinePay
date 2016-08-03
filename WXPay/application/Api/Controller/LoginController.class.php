<?php

namespace Api\Controller;

use Think\Controller;
use Api\Aes\Aes;
use Api\Service\UploadService;
/**
 * 用户注册、登陆接口
 * Class LoginController
 * @package Api\Controller
 */
class LoginController extends Controller
{
    public function __construct()
    {
        /*if(!$_POST){
            $msg['result']=array("state" => -1, "description" => "无请求参数");
            echo json_encode($msg);
            die;
        }*/
    }

    /**
     *注册接口
     */
    public function register()
    {
        $userName = I("post.username");//用户名
        $password = I("post.password");//密码
        $imei = I("post.imei");//手机串号
        $role = I("post.role", 1);//角色 0普通用户，1修理厂，2汽配档口
        if (!empty($userName) && !empty($password)) {
            $dao = M("user");
            if (!$dao->where("username=$userName")->find()) {
                $ip=$this->GetIP();
                if ($dao->add(array("username" => $userName, "password" => $password, "role" => $role, "create_time" => time(),"last_login_ip"=>$ip,"last_login_time"=>time()))) {
                    $last_id = $dao->getLastInsID();
                    $token = $this->getToken($imei, $last_id);
                    //用session保存
                    $_SESSION['user_token'] = $token;
                    $_SESSION['user_token_time'] = time();

                    $msg['data']['token'] = $token;
                    $msg['result'] = array("state" => 1, "description" => "注册成功");
                } else {
                    $msg['result'] = array("state" => -3, "description" => "注册失败");
                }
            } else {
                $msg['result'] = array("state" => -2, "description" => "该用户名已被其他人注册");
            }
        } else {
            $msg['result'] = array("state" => -1, "description" => "用户名或密码不能为空");
        }

        echo json_encode($msg);
        exit;
    }

    /**
     *登陆接口
     */
    public function login()
    {
        $imei = I("post.imei");//手机串号
        $userName = I("post.username");//用户名
        $password = I("post.password");//密码
        $userRst=M("user")->where("username=$userName and is_delete=0")->find();
        if($userRst){
            if($userRst['password']==$password){
                $user_id = $userRst['id'];
                $token = $this->getToken($imei, $user_id);
                //用session保存
                $_SESSION['user_token'] = $token;
                $_SESSION['user_token_time'] = time();
                $msg['result'] = array("state" => 1, "description" => "登陆成功");

                M("user")->where("username=$userName")->save(array("last_login_ip"=>$this->GetIP(),"last_login_time"=>time()));
            }else{
                $msg['result'] = array("state" => -2, "description" => "密码错误");
            }
        }else{
            $msg['result'] = array("state" => -1, "description" => "用户名错误");
        }
        echo json_encode($msg);
        exit;
    }

    /**
     *更新用户信息
     */
    public function updateUserInfo()
    {
        $token = I("post.token");//手机串号
        $userId=$this->getUserId($token);
        $avatar = I("post.avatar");//个人头像
        $companyPic = I("post.companyPic");//档口或厂的门面

        $nickName = I("post.nickName");//昵称
        $trueName = I("post.trueName");//真实姓名
        $gender = I("post.gender");//性别
        $age = I("post.age");//年龄
        $signature = I("post.signature");//个性签名
        $companyName = I("post.companyName");//档口或修理厂名
        $tel = I("post.tel");//联系电话，座机。有多个号码请用tel[]的形式上传
        $phone = I("post.phone");//手机号 一个
        $qq = I("post.qq");//qq号码
        $email = I("post.email");//邮箱
        $address = I("post.address");//档口或厂的地址
        $majorBusiness = I("post.majorBusiness");//档口或厂的门面
        $website = I("post.website");//档口或厂的官网

        $data=array();
        if(!empty($nickName))
            $data['nickname']=$nickName;
        if(!empty($trueName))
            $data['truename']=$trueName;
        if(!empty($gender))
            $data['gender']=$gender;
        if(!empty($age))
            $data['age']=$age;
        if(!empty($signature))
            $data['signature']=$signature;
        if(!empty($companyName))
            $data['company_name']=$companyName;
        if(!empty($tel)){
            if(is_array($tel)){
                $tel=implode(",",$tel);
            }
            $data['tel']=$tel;
        }
        if(!empty($phone))
            $data['phone']=$phone;
        if(!empty($qq))
            $data['qq']=$qq;
        if(!empty($email))
            $data['email']=$email;
        if(!empty($address))
            $data['address']=$address;
        if(!empty($majorBusiness))
            $data['major_business']=$majorBusiness;
        if(!empty($website))
            $data['website_url']=$website;

        /**
         * 保存头像
         */
        if ($_FILES['avatar']['error'] == 0) {
            //文件上传后详情的信息,原图
            $uploads = $_FILES['avatar']['name'];
            //文件保存的目录
            $dir = 'avatar';
            $images_list = UploadService::createAvatarUploads($uploads , $dir);
            $images_list = $images_list ? $images_list : '';
            $data['avatar']=$images_list;
        }

        /**
         * 保存档口门面图
         */
        if ($_FILES['companyPic']['error'] == 0) {
            //文件上传后详情的信息,原图
            $uploads = $_FILES['companyPic']['name'];
            //文件保存的目录
            $dir = 'avatar';
            $images_list = UploadService::createAvatarUploads($uploads , $dir);
            $images_list = $images_list ? $images_list : '';
            $data['company_pic_url']=$images_list;
        }

        $userDetailRst=M("userDetail")->where("user_id=$userId")->find();
        //存在就更新，不存在就插入
        if($userDetailRst){
            $rst=M("userDetail")->where("user_id=$userId")->save($data);
        }else{
            $rst=M("userDetail")->add($data);
        }
        if($rst){
            $msg['result'] = array("state" => 1, "description" => "更新用户信息成功");
        }else{
            $msg['result'] = array("state" => -1, "description" => "更新用户信息失败");
        }

        echo json_encode($msg);
        exit;
    }

    /**
     *删除用户接口
     */
    public function deleteUser()
    {
        $token = I("post.token");//手机串号
        $adminId=$this->getUserId($token);
        $userId = I("post.userId");//要删除的用户id
        $imei = I("post.imei");//手机串号
        $userRst=M("user")->where("user_id=$adminId and role=3")->find();
        if($userRst){
            $deleteRst=M("user")->where("user_id=$userId")->save(array("is_delete"=>1));
            if($deleteRst){
                $msg['result'] = array("state" => 1, "description" => "删除成功");
            }else{
                $msg['result'] = array("state" => -2, "description" => "删除失败");
            }
        }else{
            $msg['result'] = array("state" => -1, "description" => "无此用户");
        }
        echo json_encode($msg);
        exit;
    }

    /**
     *查询用户详情接口
     */
    public function queryUserInfo()
    {
        $token = I("post.token");//手机串号
        $imei = I("post.imei");//手机串号
        $userId = I("post.userId");//用户id
        if(empty($userId)){
            $userId=$this->getUserId($token);
        }

        $userRst=M("user")->where("user_id=$userId")->find();
        if($userRst){
            $data=M("userDetail")->where("user_id=$userId")->find();
            $data['userName']=$userRst['username'];
            $data['role']=$userRst['role'];
            $data['createTime']=$userRst['create_time'];
            $msg['data'] =$data;
            $msg['result'] = array("state" => 1, "description" => "查询成功");
        }else{
            $msg['result'] = array("state" => -1, "description" => "查询失败");
        }
        echo json_encode($msg);
        exit;
    }

    /**
     *查询用户列表接口
     */
    public function queryUserList()
    {
        $token = I("post.token");//手机串号
        $adminId=$this->getUserId($token);
        $role = I("post.role");//用户角色
        $userId = I("post.userId");//要删除的用户id
        $imei = I("post.imei");//手机串号
        $userRst=M("user")->where("user_id=$adminId and role=3")->find();
        if($userRst){
            $deleteRst=M("user")->where("user_id=$userId")->save(array("is_delete"=>1));
            if($deleteRst){
                $msg['result'] = array("state" => 1, "description" => "删除成功");
            }else{
                $msg['result'] = array("state" => -2, "description" => "删除失败");
            }
        }else{
            $msg['result'] = array("state" => -1, "description" => "无此用户");
        }
        echo json_encode($msg);
        exit;
    }

    /**
     * 生成新的token
     * $imei 手机串号
     * $id 用户id
     * return boolean
     * @author 黎兴济
     * @since  2016-6-15
     */
    public function getToken($imei, $id)
    {

        $data = array(
            'imei' => $imei,
            'id' => $id,
            'session_id' => $_SESSION['session_id'],
            'login_time' => time()
        );
        $da = json_encode($data);
        $crypt = new Aes();
        $token = $crypt->encode($da);
        //用session保存  $_SESSION['token']
        return $token;

    }

    /**
     * 获取用户id
     *$token string
     * return $id or false
     *
     * @author 黎兴济
     * @since  2016-6-15
     */
    public function getUserId($token)
    {
        $crypt = new Aes();
        $str = $crypt->decode($token);
        $str = json_decode($str, true);
        $id = $str['id'];
        return $id;
    }

    /**
     * 判断token是否超时  超时删除token
     * @author 黎兴济
     * @since  2016-06-15
     */
    public function isTokenExpire()
    {

    }

    /**
     * 每次正常请求就更新token的时间
     * @author 黎兴济
     * @since  2016-06-15
     */
    public function updateToken()
    {

    }

    function GetIP(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else{
            $cip = "无法获取！";
        }
        return $cip;
    }

}