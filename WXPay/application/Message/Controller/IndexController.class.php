<?php

/*
 * 
 * PHP version 5.5
 *
 * @copyright  Copyright (c) 2012-2015
 * @link       http://www.37service.com
 * @license    广州37度网络科技版权所有
 */
namespace Message\Controller;

use Message\Logic\Message;
use Think\Controller;
use Message\Aes\Aes;

/**
 * 测试短信通知
 *
 * @author    张涛
 * @since     2015年7月19日
 * @version   1.0
 */
class IndexController extends Controller
{
	/**
	 * 测试短信通知的调用
	 *
	 *
	 * @author 张涛
	 * @since  2015年7月19日
	 */
	public function test()
	{
		$data = array('content' => array('code' => randomString(6 , 'num')));
		$a = Message::getInstance()->send('101-0-1' , 'phone' , '13610156719' , $data , 19);
		dump($a);
	}

	public function index()
	{
		$token = I("post.token");//手机串号
		if(!$token){
			$token= I("get.token");
		}
		$userId = $this->getUserId($token);
		$this->getAuth($userId);
		$sqlPre="sp_";
		$userCount=M("user_sleep",$sqlPre)->count();
		$shopCount=M("shop_sleep",$sqlPre)->count();
		$brandCount=M("brand_sleep",$sqlPre)->count();
		$recordCount=M("record_sleep",$sqlPre)->count();
		$this->assign("userCount",$userCount?$userCount:0);
		$this->assign("shopCount",$shopCount?$shopCount:0);
		$this->assign("brandCount",$brandCount?$brandCount:0);
		$this->assign("recordCount",$recordCount?$recordCount:0);
		$this->display("Index:pages/index");
	}

	public function login()
	{
		$this->display("Index:pages/login");
	}

	public function register()
	{
		$this->display("Index:pages/register");
	}

	public function modifyPsw()
	{
		$this->display("Index:pages/modifyPsw");
	}

	public function addBrand()
	{
		$this->display("Index:pages/add_brand");
	}
	public function addShop()
	{
		$this->display("Index:pages/add_shop");
	}


	public function brandList()
	{
		$this->display("Index:pages/brand_list");
	}

	public function shopList()
	{
		$this->display("Index:pages/shop_list");
	}

	public function shopInfo()
	{
		$this->display("Index:pages/shopInfo");
	}

	public function userList()
	{
		$this->display("Index:pages/user_list");
	}

	public function statisticsList()
	{
		$this->display("Index:pages/statistics_list");
	}

	public function recordMoreDetail(){
		$this->display("Index:pages/statistics_more_list");
	}




	/**
	 *  查询app版本更新接口
	 * @author  黎兴济
	 * @since  2016-6-16
	 */
	public function appUpdate()
	{
		$msg['android']['versioncode'] = "1";//内部版本号
		$msg['android']['versionname'] = "V0.1";//外部版本号
		$msg['android']['downloadurl'] = "http://test.37service.com/application/Message/Common/App/sleeplessFlower-1.0.apk";//"http://test.37service.com/data/upload/App/sleeplessFlower.apk";//下载地址
		$msg['android']['updatelog'] = "";//更新日志
		$msg['android']['coverversion'] = 0;//强制覆盖更新版本号，强制覆盖该版本之前的所有版本

		echo json_encode($msg);
		exit;
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

	public function getAuth($userId)
	{
		if ( !empty($userId)) {
			$userRst = M("user_sleep","sp_")->where("id=$userId and role=3")->find();
			if (empty($userRst)) {
				echo "<h2>您没有管理员操作权限</h2><script>setTimeout(function(){location.href='login.html';},2000)</script>";
				exit;
			}
		}
		else {
			echo "<h2>您没有管理员操作权限</h2><script>setTimeout(function(){location.href='login.html';},2000)</script>";
			exit;
		}
	}

}