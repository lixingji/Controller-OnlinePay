<?php

namespace Message\Controller;

use Think\Controller;
use Message\Service\UploadService;
use Message\Controller\LoginController;

/**
 * shop
 * Class LoginController
 * @package Api\Controller
 */
class ShopController extends Controller
{
    public function __construct()
    {
        if ( !$_POST) {
            $msg['result'] = array("state" => -1 , "description" => "无请求参数");
            echo json_encode($msg);
            die;
        }
//        header('Access-Control-Allow-Origin:*');
    }

    /**
     * 添加品牌
     */
    public function addBrand()
    {
        $token = I("post.token");
        $imei = I("post.imei");//手机串号
        $brandName = I("post.brandname");//
        $login = new LoginController();
        $userId = $login->getUserId($token);

        $this->getAuth($userId);
        /**
         * 保存图片
         */
        if ($_FILES['brand']['name']) {
            //文件上传后详情的信息,原图
            $uploads = $_FILES['brand']['name'];
            //文件保存的目录
            $dir = 'brand';
            $images_list = UploadService::createUploads($uploads , $dir);
            $images_list = $images_list ? $images_list : '';
            if (is_array($images_list)) {
                $images_list = $images_list[0];
            }
            $data['logo_url'] = $images_list;
        }
        $data['brandname'] = $brandName;
        $brandRst = M("brand_sleep" , "sp_")->add($data);
        if ($brandRst) {
            $msg['result'] = array("state" => 1 , "description" => "添加品牌成功");
        }
        else {
            $msg['result'] = array("state" => -1 , "description" => "添加品牌失败");
        }
        echo "<h2>" . $msg['result']['description'] . "</h2>";
        echo "<script>setTimeout(function(){history.go(-1);},2000)</script>";
        exit;
    }

    /**
     * 删除品牌
     */
    public function deleteBrand()
    {
        $token = I("post.token");
        $imei = I("post.imei");//手机串号
        $brandId = I("post.brandId");//手机串号
        $login = new LoginController();
        $userId = $login->getUserId($token);
        //权限
        $this->getAuth($userId);

        $data = M("brand_sleep" , "sp_")->where("id=$brandId")->find();
        if ($data) {
            M("brand_sleep" , "sp_")->where("id=$brandId")->delete();
            $msg['result'] = array("state" => 1 , "description" => "删除成功");
        }
        else {
            $msg['result'] = array("state" => -1 , "description" => "没有品牌数据");
        }
        echo json_encode($msg);
        exit;
    }

    /**
     * 查询车辆品牌列表  普通游客可见
     */
    public function queryBrandList()
    {
        $brandData = M("brand_sleep" , "sp_")->select();
        if ($brandData) {
            foreach ($brandData as $key => $value) {
                $data[$key]['id'] = $value['id'];
                $data[$key]['brandName'] = $value['brandname'];
                $data[$key]['logoUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $value['logo_url'];
                unset($value['brandname']);
                unset($value['logo_url']);
            }
            $msg['data'] = $data;
            $msg['result'] = array("state" => 1 , "description" => "查询品牌数据成功");
        }
        else {
            $msg['result'] = array("state" => -1 , "description" => "没有品牌数据");
        }
        echo json_encode($msg);
        exit;
    }

    /**
     *查询商店列表
     */
    public function queryShopList()
    {
        $type = I("post.type" , 0);//类型。0表示全部，1是档口新车件，2是档口拆车件，3是修理厂
        $pageSize = I("post.pageSize" , 10);
        $pageIndex = I("post.pageIndex" , 0);
        if($type!=0){
            $condition="type=$type";
        }else{
            $condition="1=1";
        }
        $shopData = M("shop_sleep" , "sp_")->where($condition)->order("shop_order ASC")->limit($pageSize * $pageIndex , $pageSize)->select();
        if ($shopData) {
            $count = M("shop_sleep" , "sp_")->where($condition)->count();
            //'http://' . $_SERVER['HTTP_HOST']
            foreach ($shopData as $key => $value) {
                $data[$key]['id'] = $value['id'];
                $data[$key]['icon'] = 'http://' . $_SERVER['HTTP_HOST'] . $value['icon'];
                $data[$key]['name'] = $value['name'];
                //				$data[$key]['description'] = $value['description'];
                $data[$key]['address'] = $value['address'];
                /*$data[$key]['website'] = $value['website'];
                $data[$key]['advance'] = $value['advance'];
                $data[$key]['type'] = $value['type'];
                $phoneList = explode("," , $value['phone_list']);
                $data[$key]['phoneList'] = $phoneList;
                $mobileList = explode("," , $value['mobile_list']);
                $data[$key]['mobileList'] = $mobileList;
                $imageUrl = explode("," , $value['image_url']);
                foreach($imageUrl as $k=>$v){
                    $data[$key]['imageUrl'][$k] = 'http://' . $_SERVER['HTTP_HOST'] .$v;
                }*/
            }
            $msg['count'] = $count;
            $msg['data'] = $data;
            $msg['result'] = array("state" => 1 , "description" => "查询商店数据成功");
        }
        else {
            $msg['result'] = array("state" => -1 , "description" => "没有商店数据");
        }
        echo json_encode($msg);
        exit;
    }


    /**
     *查询商店详情
     */
    public function queryShopDetail()
    {
        $token = I("post.token");
        $imei = I("post.imei");//手机串号
        $shopId = I("post.shopId");//
        $address = I("post.address");//客户端地址
        $login = new LoginController();
        $userId = $login->getUserId($token);

        if ($userId) {
            $shopRst = M("shop_sleep" , "sp_")->where("id=$shopId")->find();
            if ($shopRst['type'] == 1) {
                $userRst = M("user_sleep" , "sp_")->where("id=$userId and can_see_factory=1")->find();
                if ( !$userRst) {
                    $msg['result'] = array("state" => -1 , "description" => "您没有查看权限，请联系管理员申请权限");
                    echo json_encode($msg);
                    exit;
                }
            }
        }
        else {
            $msg['result'] = array("state" => -1 , "description" => "您没有查看权限，请联系管理员申请权限.");
            echo json_encode($msg);
            exit;
        }
        if ($shopRst) {
            $data['id'] = $shopRst['id'];
            $data['icon'] = 'http://' . $_SERVER['HTTP_HOST'] . $shopRst['icon'];
            $data['name'] = $shopRst['name'];
            $data['description'] = $shopRst['description'];
            $data['address'] = $shopRst['address'];
            $data['website'] = $shopRst['website'];
            $data['advance'] = $shopRst['advance'];
            $data['type'] = $shopRst['type'];//类型
            $data['shopOrder'] = $shopRst['shop_order'];//排序
            $phoneList = explode("," , $shopRst['phone_list']);
            $data['phoneList'] = $phoneList;
            $mobileList = explode("," , $shopRst['mobile_list']);
            $data['mobileList'] = $mobileList;
            $imageUrl = explode("," , $shopRst['image_url']);
            foreach ($imageUrl as $k => $v) {
                $data['imageUrl'][$k] = 'http://' . $_SERVER['HTTP_HOST'] . $v;
            }
            $msg['data'] = $data;
            $msg['result'] = array("state" => 1 , "description" => "查询商店详情成功");
             M("record_sleep","sp_")->add(array("shop_id"=>$shopId,"user_id"=>$userId,"client_ip"=>$this->GetIP(),"client_click_time"=>time(),"client_address"=>$address?$address:"广东广州天河","shop_name"=>$data['name']));
        }
        else {
            $msg['result'] = array("state" => 1 , "description" => "无此商店");
        }
        echo json_encode($msg);
        exit;
    }

    /**
     *添加商店
     */
    public function addShop()
    {
        $token = I("post.token");
        $imei = I("post.imei");//手机串号
        $icon = I("post.icon");//商店图片
        $name = I("post.name");//商店名称
        $description = I("post.description");//商店简介
        $address = I("post.address");//商店地址
        $website = I("post.website");//商店网址
        $advance = I("post.advance");//主营业务
        $phoneList = I("post.phoneList");//电话
        $mobileList = I("post.mobileList");//手机号
        $imageUrl = I("post.imageUrl[]");//用来显示横幅的图片列表
        $type = I("post.type" , 1);//类型。1是档口新车件，2是档口拆车件，3是修理厂
        $shopOrder = I("post.shopOrder" , 1);//排序，从0开始，不给钱的放到1
        $login = new LoginController();
        $userId = $login->getUserId($token);


        //处理，中文字符转英文
        $phoneList = str_replace("，" , "," , $phoneList);
        $mobileList = str_replace("，" , "," , $mobileList);
        //权限
        $this->getAuth($userId);

        $data = array("name" => $name , "description" => $description , "website" => $website , "address" => $address , "advance" => $advance , "phone_list" => $phoneList , "mobile_list" => $mobileList , 'create_time' => time() , "type" => $type , "shop_order" => $shopOrder);
        /**
         * 保存图片
         */
        if ($_FILES['icon']['name']) {
            //文件上传后详情的信息,原图
            $uploads = $_FILES['icon']['name'];
            //文件保存的目录
            $dir = 'shop';
            $images_list = UploadService::createUploads($uploads , $dir);
            $images_list = $images_list ? $images_list : '';
            if (is_array($images_list)) {
                $data['icon'] = $images_list[0];
                if (sizeof($images_list) > 1) {
                    $imageUrl_list = array_shift($images_list);//删除第一个元素
                    $data['image_url'] = implode("," , $imageUrl_list);
                }
            }
            else {
                $data['icon'] = $images_list;
            }
        }

        $shopRst = M("shop_sleep" , "sp_")->add($data);
        if ($shopRst) {
            $msg['result'] = array("state" => 1 , "description" => "添加商店成功");
        }
        else {
            $msg['result'] = array("state" => -1 , "description" => "添加商店失败");
        }
        echo "<h2>" . $msg['result']['description'] . "</h2>";
        echo "<script>setTimeout(function(){history.go(-1);},2000)</script>";
        exit;
    }

    /**
     *删除商店
     */
    public function deleteShop()
    {
        $token = I("post.token");
        $imei = I("post.imei");//手机串号
        $shopId = I("post.shopId");//
        $login = new LoginController();
        $userId = $login->getUserId($token);
        //权限
        $this->getAuth($userId);

        $data = M("shop_sleep" , "sp_")->where("id=$shopId")->find();
        if ($data) {
            M("shop_sleep" , "sp_")->where("id=$shopId")->delete();
            $msg['result'] = array("state" => 1 , "description" => "删除成功");
        }
        else {
            $msg['result'] = array("state" => -1 , "description" => "没有品牌数据");
        }
        echo json_encode($msg);
        exit;
    }

    /**
     *更新商店信息详情
     */
    public function updateShop()
    {
        $token = I("post.token");
        $imei = I("post.imei");//手机串号
        $id = I("post.shopId");//商店id
        $icon = I("post.icon");//商店图片
        $name = I("post.name");//商店名称
        $description = I("post.description");//商店简介
        $address = I("post.address");//地址
        $website = I("post.website");//商店网址
        $advance = I("post.advance");//主营业务
        $phoneList = I("post.phoneList");//电话
        $mobileList = I("post.mobileList");//手机号
        $imageUrl = I("post.imageUrl[]");//用来显示横幅的图片列表
        $type = I("post.type" , 0);//类型。0是档口，1是修理厂
        $shopOrder = I("post.shopOrder" , 1);//排序，从0开始，不给钱的放到1
        $login = new LoginController();
        $userId = $login->getUserId($token);
        //权限
        $this->getAuth($userId);

        //处理，中文字符转英文
        $phoneList = str_replace("，" , "," , $phoneList);
        $mobileList = str_replace("，" , "," , $mobileList);

        $data = array("name" => $name , "description" => $description , "website" => $website , "address" => $address , "advance" => $advance , "phone_list" => $phoneList , "mobile_list" => $mobileList , 'create_time' => time() , "type" => $type , "shop_order" => $shopOrder);
        /**
         * 保存图片
         */
        if ($_FILES['icon']['name']) {
            //文件上传后详情的信息,原图
            $uploads = $_FILES['icon']['name'];
            //文件保存的目录
            $dir = 'shop';
            $images_list = UploadService::createUploads($uploads , $dir);
            $images_list = $images_list ? $images_list : '';
            if (is_array($images_list)) {
                $data['icon'] = $images_list[0];
                if (sizeof($images_list) > 1) {
                    $imageUrl_list = array_shift($images_list);//删除第一个元素
                    $data['image_url'] = implode("," , $imageUrl_list);
                }
            }
            else {
                $data['icon'] = $images_list;
            }
        }
        $shopRst = M("shop_sleep" , "sp_")->where("id=$id")->save($data);
        if ($shopRst) {
            $msg['result'] = array("state" => 1 , "description" => "更新商店成功");
        }
        else {
            $msg['result'] = array("state" => -1 , "description" => "更新商店失败");
        }
        echo "<h2>" . $msg['result']['description'] . "</h2>";
        echo "<script>setTimeout(function(){history.go(-1);},2000)</script>";
        exit;
    }

    public function queryRecordList()
    {
        $pageIndex = I("post.pageIndex",0);
        $pageSize = I("post.pageSize",10);//手机串号
        $sql = "Select id,shop_id,shop_name,count(shop_id) as count from sp_record_sleep group by shop_id limit ".$pageIndex*$pageSize.",".$pageSize;
        $rst = M()->query($sql);
        if($rst){
            $sql = "Select id,shop_id,shop_name,count(shop_id) as count from sp_record_sleep group by shop_id ";
            $rst = M()->query($sql);
            $msg['count'] =sizeof($rst);
            $msg['data'] =$rst;
            $msg['result'] = array("state" => 1, "description" => "获取成功");
        }else{
            $msg['result'] = array("state" => -1, "description" => "无数据");
        }
        echo json_encode($msg);
        exit;
    }

    public function queryRecordDetail()
    {
        $shopId = I("post.shopId",0);
        $rst = M("record_sleep","sp_")->where("shop_id=$shopId")->select();
        if($rst){
            foreach($rst as $key=>$value){
                $rst[$key]['client_click_time']=date("Y-m-d H:m:s",$rst[$key]['client_click_time']);
            }
            $msg['count']= M("record_sleep","sp_")->where("shop_id=$shopId")->count();
            $msg['data'] =$rst;
            $msg['result'] = array("state" => 1, "description" => "获取成功");
        }else{
            $msg['result'] = array("state" => -1, "description" => "无数据");
        }
        echo json_encode($msg);
        exit;
    }


    public function getAuth($userId)
    {
        if (!empty($userId)) {
            $userRst = M("user_sleep", "sp_")->where("id=$userId and role=3")->find();
            if (!$userRst) {
                $msg['result'] = array("state" => -1, "description" => "您无此操作权限");
                echo json_encode($msg);
                exit;
            }
        } else {
            $msg['result'] = array("state" => -1, "description" => "您无此操作权限");
            echo json_encode($msg);
            exit;
        }
    }

    function GetIP()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = "无法获取！";
        }
        return $cip;
    }

}