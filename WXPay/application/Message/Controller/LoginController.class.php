<?php

namespace Message\Controller;

use Think\Controller;
use Message\Aes\Aes;
use Message\Service\UploadService;

/**
 * 用户注册、登陆接口
 * Class LoginController
 * @package Api\Controller
 */
class LoginController extends Controller
{

	public function __construct()
	{
		if ( !$_POST) {
			$msg['result'] = array("state" => -1 , "description" => "无请求参数");
			echo json_encode($msg);
			die;
		}
	}

	/**
	 *注册接口
	 */
	public function register()
	{
		$userName = I("post.username");//用户名
		$password = I("post.password");//密码
		$imei = I("post.imei");//手机串号
		$role = I("post.role" , 1);//角色 0普通用户，1修理厂，2汽配档口
		if ($role >= 3) {
			$msg['result'] = array("state" => -1001 , "description" => "你申请的角色非法，请确认再提交");
			echo json_encode($msg);
			exit;
		}
		if ( !empty($userName) && !empty($password)) {
			$dao = M("user_sleep","sp_");
			if ( !$dao->where("username=$userName")->find()) {
				$ip = $this->GetIP();
				if ($dao->add(array("username" => $userName , "password" => $password , "role" => $role , "create_time" => time() , "last_login_ip" => $ip , "last_login_time" => time()))) {
					$last_id = $dao->getLastInsID();
					$token = $this->getToken($imei , $last_id);
					//用session保存
					$_SESSION['user_token'] = $token;
					$_SESSION['user_token_time'] = time();

					$msg['data']['token'] = $token;
					$msg['result'] = array("state" => 1 , "description" => "注册成功");
				}
				else {
					$msg['result'] = array("state" => -3 , "description" => "注册失败");
				}
			}
			else {
				$msg['result'] = array("state" => -2 , "description" => "该用户名已被其他人注册");
			}
		}
		else {
			$msg['result'] = array("state" => -1 , "description" => "用户名或密码不能为空");
		}

		echo json_encode($msg);
		exit;
	}

	/**
	 *登陆接口
	 */
	public function login()
	{
		$imei = I("post.imei","88886666");//手机串号
		$userName = I("post.username");//用户名
		$password = I("post.password");//密码
		$userRst = M("user_sleep","sp_")->where("username='$userName' and is_delete=0")->find();
		if ($userRst) {
			if ($userRst['password'] == $password) {
				$user_id = $userRst['id'];
				$token = $this->getToken($imei , $user_id);
				//用session保存
				$_SESSION['user_token'] = $token;
				$_SESSION['user_token_time'] = time();
				$msg['result'] = array("state" => 1 , "description" => "登陆成功");
				$msg['data']['token'] = $token;
				M("user_sleep","sp_")->where("username='$userName'")->save(array("last_login_ip" => $this->GetIP() , "last_login_time" => time()));
			}
			else {
				$msg['result'] = array("state" => -2 , "description" => "密码错误");
			}
		}
		else {
			$msg['result'] = array("state" => -1 , "description" => "用户名错误");
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
		$userId = $this->getUserId($token);
		$avatar = I("post.avatar");//个人头像
		$nickName = I("post.nickName");//昵称
		$trueName = I("post.trueName");//真实姓名
		$gender = I("post.gender");//性别
		$age = I("post.age");//年龄
		$signature = I("post.signature");//个性签名
		$phone = I("post.phone");//手机号 一个

		if (empty($userId)) {
			$msg['result'] = array("state" => -1 , "description" => "用户id不存在");
			echo json_encode($msg);
			exit;
		}


		$data = array();
		if ( !empty($nickName))
			$data['nickname'] = $nickName;
		if ( !empty($trueName))
			$data['truename'] = $trueName;
		if ( !empty($gender))
			$data['gender'] = $gender;
		if ( !empty($age))
			$data['age'] = $age;
		if ( !empty($signature))
			$data['signature'] = $signature;
		if ( !empty($phone))
			$data['phone'] = $phone;

		/**
		 * 保存头像
		 */
		if ($_FILES['avatar']['name']) {
			//文件上传后详情的信息,原图
			$uploads = $_FILES['avatar']['name'];
			//文件保存的目录
			$dir = 'avatar';
			$images_list = UploadService::createUploads($uploads , $dir);
			$images_list = $images_list ? $images_list : '';
			$data['avatar'] = $images_list;
		}

		$userDetailRst = M("userDetailSleep")->where("user_id=$userId")->find();
		//存在就更新，不存在就插入
		if ($userDetailRst) {
			$rst = M("userDetailSleep")->where("user_id=$userId")->save($data);
		}
		else {
			$rst = M("userDetailSleep")->add($data);
		}
		if ($rst) {
			$msg['result'] = array("state" => 1 , "description" => "更新用户信息成功");
		}
		else {
			$msg['result'] = array("state" => -1 , "description" => "更新用户信息失败");
		}

		echo json_encode($msg);
		exit;
	}

	/**
	 *查询用户详情接口
	 */
	public function queryUserInfo()
	{
		$token = I("post.token");
		$imei = I("post.imei");//手机串号
		$userId = I("post.userId");//用户id，管理员查用户详情
		if (empty($userId)) {
			$userId = $this->getUserId($token);//用户自己查详情
		}

		$userRst = M("user_sleep","sp_")->where("user_id=$userId")->find();
		if ($userRst) {
			$data = M("userDetailSleep")->where("user_id=$userId")->find();
			$data['userName'] = $userRst['username'];
			$data['role'] = $userRst['role'];
			$data['createTime'] = $userRst['create_time'];
			$msg['data'] = $data;
			$msg['result'] = array("state" => 1 , "description" => "查询成功");
		}
		else {
			$msg['result'] = array("state" => -1 , "description" => "查询失败");
		}
		echo json_encode($msg);
		exit;
	}

	/**
	 *修改用户密码接口
	 */
	public function modifyPsw()
	{
		$token = I("post.token");
		$imei = I("post.imei");//手机串号
		$userId = $this->getUserId($token);//用户自己查详情
		$oldPsw = I("post.oldPsw");//原密码
		$newPsw = I("post.newPsw");//新密码

		$userRst = M("user_sleep","sp_")->where("id=$userId")->find();
		if ($userRst) {
			if ($userRst['password'] == $oldPsw) {
				if (M("user_sleep","sp_")->where("id=$userId")->save(array("password" => $newPsw))) {
					$msg['result'] = array("state" => 1 , "description" => "修改密码成功");
				}
				else {
					$msg['result'] = array("state" => -3 , "description" => "修改密码失败");
				}
			}
			else {
				$msg['result'] = array("state" => -2 , "description" => "原密码有误");
			}
		}
		else {
			$msg['result'] = array("state" => -1 , "description" => "token出错");
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
		$adminId = $this->getUserId($token);
		$num = I("post.num"); //num 1允许看修理厂  2不允许  3删除
		$userId = I("post.userId");//要删除的用户id
		$imei = I("post.imei");//手机串号
		$userRst = M("user_sleep","sp_")->where("id=$adminId and role=3")->find();
		if ($userRst) {
			$description=$num==3?"删除":"保存";
			if($num==3){
				$deleteRst = M("user_sleep","sp_")->where("id=$userId")->save(array("is_delete" => 1));
			}else{
				$deleteRst = M("user_sleep","sp_")->where("id=$userId")->save(array("can_see_factory" => $num==1?1:0));
			}

			if ($deleteRst) {
				$msg['result'] = array("state" => 1 , "description" => $description."成功");
			}
			else {
				$msg['result'] = array("state" => -2 , "description" => $description."失败");
			}
		}
		else {
			$msg['result'] = array("state" => -1 , "description" => "请先登陆管理员账号才能操作用户");
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
		$adminId = $this->getUserId($token);
		$pageSize = I("post.pageSize",10);//每页显示数量
		$pageIndex = I("post.pageIndex",0);//页码，从0开始
		$imei = I("post.imei");//手机串号
		$userRst =  M("user_sleep","sp_")->where("id=$adminId and role=3")->find();
		if ($userRst) {
			$userList =  M("user_sleep","sp_")->where("role<3 and is_delete=0")->limit($pageIndex*$pageSize,$pageSize)->select();
			$count =  M("user_sleep","sp_")->where("role<3 and is_delete=0")->count();
			if ($userList) {
				foreach($userList as $k=>$v){
					unset($v['password']);
					$userList[$k]['last_login_time']=date('Y-m-d H:m:s',$v['last_login_time']);
				}
				$msg['count']=$count;
				$msg['data']=$userList;
				$msg['result'] = array("state" => 1 , "description" => "查询成功");
			}
			else {
				$msg['result'] = array("state" => -2 , "description" => "无用户数据");
			}
		}
		else {
			$msg['result'] = array("state" => -1 , "description" => "您不是管理员，请用管理员身份登陆");
		}
		echo json_encode($msg);
		exit;
	}

	public function getAuth($userId)
	{
		if ( !empty($userId)) {
			$userRst = M("user_sleep","sp_")->where("id=$userId and role=3")->find();
			if ( !$userRst) {
				$msg['result'] = array("state" => -1 , "description" => "您无此操作权限");
				echo json_encode($msg);
				exit;
			}
		}
		else {
			$msg['result'] = array("state" => -1 , "description" => "您无此操作权限");
			echo json_encode($msg);
			exit;
		}
	}

	/**
	 * 生成新的token
	 * $imei 手机串号
	 * $id 用户id
	 * return boolean
	 * @author 黎兴济
	 * @since  2016-6-15
	 */
	public function getToken($imei , $id)
	{
		$data = array('imei' => $imei , 'id' => $id , 'session_id' => $_SESSION['session_id'] , 'login_time' => time());
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
		$str = json_decode($str , true);
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

	function GetIP()
	{
		if ( !empty($_SERVER["HTTP_CLIENT_IP"])) {
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif ( !empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif ( !empty($_SERVER["REMOTE_ADDR"])) {
			$cip = $_SERVER["REMOTE_ADDR"];
		}
		else {
			$cip = "无法获取！";
		}
		return $cip;
	}

}