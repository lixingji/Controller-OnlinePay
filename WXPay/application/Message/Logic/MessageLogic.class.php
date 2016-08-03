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

/**
 * 通知（手机短信、邮件）
 * 
 * @author    张涛
 * @since     2015-7-17
 * @version   1.0
 */
class MessageLogic extends \Think\Model
{

    /**
     * 通知类型
     * @var $types
     */
    protected static $types = array('1' => 'phone', '2' => 'email');

    /**
    * 发送
    * 
    * 
    * @param string $tpl  模板
    * @param string $type 类型
    * @param array $data  内容
    * @author 张涛
    * @since  2015-7-17
    */
    public function send($tpl, $type, array $data)
    {
        $type=trim($type);
        if(!in_array($type, self::$types)){
        	return false;
        }else{
//        	$obj=
        };
    }

    /**
     * 解析通知模板
     * 
     * 
     * @author 张涛
     * @since  2015-7-17
     */
    public function parseNoticeTpl()
    {
    }
}
?>