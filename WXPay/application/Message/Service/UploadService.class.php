<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
namespace Message\Service;

use Common\Common;
use Admin\Dao;
use Admin\Model;
use Admin\Dao\PostBarDao;
use Admin\Service\ThumbService;

class UploadService
{
    public static function createUploads($uploads, $dir)
    {
        if ($uploads) {
            //$uploads path
            $uploadPath = str_replace('\\', '/', SITE_PATH) . 'application/Message/Common/' . $dir . '/';
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath);
            }
            $config = array(
                'rootPath' => $uploadPath,
                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                'autoSub' => true,
                'subName' => array('date', 'Ymd'),
            );
            $upload = new \Think\Upload($config);
            $infos = $upload->upload();
            $upload_path = I('post.upload_path', '');
            $img=array();
            if (!empty($infos)) {
                foreach ($infos as $info) {
                    $img[] = '/application/Message/Common/' . $dir . '/' . $info['savepath'] . $info['savename'];
                }
            } else {
                $img = $upload_path;
            }
            return $img;
        } else {

        }
    }


    //
    public static function  createApp($fields)
    {
        //$uploads path
        $uploadPath = str_replace('\\', '/', SITE_PATH) . 'application/Message/Common/';
        $config = array(
            'FILE_UPLOAD_TYPE' => sp_is_sae() ? "Sae" : 'Local',
            'rootPath' =>$uploadPath ,
            'savePath' => './App/',
            'saveName' => "sleeplessFlower-1.0",
            'exts' => array('apk'),
            'autoSub' => false,
            'replace' => true
        );
        $upload = new \Think\Upload($config);
        //
        $info = $upload->uploadOne($fields);
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/application/Message/Common/App/sleeplessFlower-1.0.apk';
        return $url;
    }

}