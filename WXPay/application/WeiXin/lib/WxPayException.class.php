<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/19 0019
 * Time: 下午 5:07
 */

namespace WeiXin\lib;


class WxPayException extends \Exception{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}