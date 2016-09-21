<?php

/**
 * 微信内网页支付插件
 * $Author: yolin $
 */

if (!defined('IWCHINA'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/weixinjs.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'weixinjs_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'yolin';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.weixin.qq.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'weixin_mchid',     'type' => 'text', 'value' => ''),
        array('name' => 'weixin_appid',     'type' => 'text', 'value' => ''),
        array('name' => 'weixin_appsecert', 'type' => 'text', 'value' => ''),
        array('name' => 'weixin_key',       'type' => 'text', 'value' => '')
    );

    return;
}

/**
 * 类
 */
class weixinjs{

	/**
	 * 构造函数
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	function weixinjs(){
	}

	function __construct(){
		$this->weixinjs();
	}

	/**
	 * 生成支付代码
	 * @param   array   $order      订单信息
	 * @param   array   $payment    支付方式信息
	 */
	function get_code($order, $payment, $type = ''){
		header('Location:'. str_replace(CUR_PATH.'/', '', $GLOBALS['ecs']->url()) . 'includes/modules/payment/WxpayAPI/jsapi.php?order_id='.$order['order_id']);
		exit;
	}
}
?>