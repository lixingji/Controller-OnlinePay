<?php

/*
 *
 * PHP version 5.5
 *
 * @copyright  Copyright (c) 2012-2015
 * @link       http://www.37service.com
 * @license    广州37度网络科技版权所有
 */
namespace Message\Logic;
use Think\Log;

/**
 * 通知（手机短信、邮件）
 * 
 * @author    张涛
 * @since     2015-7-17
 * @version   1.0
 */
class Message
{

    /**
     * 通知类型
     * @var $types
     */
    protected static $types = array('1' => 'Phone', '2' => 'Email');

    /**
     * 实例
     * @var unknown
     */
    private static $instance = null;

    /**
     * 获取单个实例
     * 
     * 
     * @return \Message\Logic\Message
     * @author 张涛
     * @since  2015年7月19日
     */
    public static function getInstance()
    {
        return self::$instance == null ? new Message() : self::$instance;
    }

        /**
     * 发送
     * 
     * 
     * @param string $tpl  模板
     * @param string $type 类型
     * @param array $data  内容
     * @param int $uid  用户id
     * @author 张涛
     * @since  2015-7-17
     */
    public function send($tplIndex, $type, $reciver, array $data, $uid = '')
    {
        $type = ucwords(trim($type));
        //echo "$type";
        if (! in_array($type, self::$types)) {
            //没有该通知类型
            return array('status' => 0, 'info' => L('NOT_SUPPORT_TYPE'));
        } else {
            $tplIndex = explode('-', $tplIndex);
            $tplContent = $this->getTplContent($type, $tplIndex, $data);
            $tplContent['content'] = $tplContent['content']."[三七度服务]";
            $IP = GetIP();
            Log::record("短信请求 IP = $IP");
            //防止短信炸弹
            /*if (D('Message/SmssendLog')->sendSmsOften($reciver, 60)) {
                return array('state' => -1016, 'description' => '操作频繁，请稍后再试');
            }*/
            if ($tplContent) {
                $className = '\\Message\\Logic\\Type\\' . $type;
                return $className::send($reciver, $tplContent, $uid);
            } else {
                return array('status' => 0, 'info' => L('TPL_NOT_FOUND'));
            }
        }
    }

    /**
     * 获取通知模板
     * 
     * 
     * @param string $type
     * @author 张涛
     * @since  2015年7月19日
     */
    public function getTplList($type)
    {
        // 获取模板
        $typeClass = '\\Message\\Logic\\Type\\' . ucwords($type);
        return $typeClass::$tplList;
    }

    /**
     * 解析通知模板数据
     * 
     * 
     * @param string $type
     * @param array $tplIndex
     * @param array $data
     * @return string|mixed
     * @author 张涛
     * @since  2015年7月19日
     */
    public function getTplContent($type, array $tplIndex, array $data)
    {
        // 获取模板
        $tplList = $this->getTplList($type);
        if (empty($tplList[$tplIndex[0]]['data'][$tplIndex[1]][$tplIndex[2]])) {
            //模板不存在
            return false;
        }
        $tpl = $tplList[$tplIndex[0]]['data'][$tplIndex[1]][$tplIndex[2]];
        
        // 保存$tplIndex
        $tpl['tplIndex'] = $tplIndex;
        $tpl['verifyCode'] = ! empty($data['content']['code']) ? $data['content']['code'] : '';
        // 替换数据
        if (! empty($tpl['title']) && ! empty($data['title']) && is_array($data['title'])) {
            foreach ($data['title'] as $k => $v) {
                $tpl['title'] = str_replace('{{' . $k . '}}', $v, $tpl['title']);
            }
        }
        if (! empty($tpl['content']) && ! empty($data['content']) && is_array($data['content'])) {
            foreach ($data['content'] as $k => $v) {
                $tpl['content'] = str_replace('{{' . $k . '}}', $v, $tpl['content']);
            }
        }
        return $tpl;
    }
}
?>