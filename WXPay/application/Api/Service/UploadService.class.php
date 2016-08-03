<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
namespace Api\Service;

use Common\Common;
use Admin\Dao;
use Admin\Model;
use Admin\Dao\PostBarDao;
use Admin\Service\ThumbService;

class UploadService
{
    public static function createAvatarUploads($uploads, $dir)
    {
        if ($uploads) {
            //$uploads path
            $uploadPath = str_replace('\\', '/', SITE_PATH) . 'data/upload/' . $dir . '/';
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
            $upload_path = I('post.avatar', '');
            if (!empty($infos)) {
                foreach ($infos as $info) {
                    $img = '/data/upload/' . $dir . '/' . $info['savepath'] . $info['savename'];
                }
//                $img = '/data/upload/'.$dir.'/' . $infos['savepath'] . $infos['savename'];
            } else {
                $img = $upload_path;
            }
            $logo = $img;
            return $logo;
        } else {

        }
    }

    public static function createCompanyUploads($uploads, $dir)
    {
        if ($uploads) {
            //$uploads path
            $uploadPath = str_replace('\\', '/', SITE_PATH) . 'data/upload/' . $dir . '/';
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
            $upload_path = I('post.companyPic', '');
            if (!empty($infos)) {
                foreach ($infos as $info) {
                    $img = '/data/upload/' . $dir . '/' . $info['savepath'] . $info['savename'];
                }
                //                $img = '/data/upload/'.$dir.'/' . $infos['savepath'] . $infos['savename'];
            } else {
                $img = $upload_path;
            }
            $logo = $img;
            return $logo;
        } else {

        }
    }

    //
    public static function  createApp($fields)
    {
        $config = array(
            'FILE_UPLOAD_TYPE' => sp_is_sae() ? "Sae" : 'Local',
            'rootPath' => './' . C("UPLOADPATH"),
            'savePath' => './App/',
            'saveName' => "xiaohushi-1.0",
            'exts' => array('apk'),
            'autoSub' => false,
            'replace' => true
        );
        $upload = new \Think\Upload($config);
        // �ϴ������ļ�
        $info = $upload->uploadOne($fields);
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/data/upload/App/xiaohushi-1.0.apk';
        return $url;
    }

}