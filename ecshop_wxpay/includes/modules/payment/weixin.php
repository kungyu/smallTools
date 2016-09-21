<?php

/**
 * 微信扫码支付插件
 * $Author: yolin $
 */

if (!defined('IWCHINA'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/weixin.php';

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
    $modules[$i]['desc']    = 'weixin_desc';

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
class weixin{

	/**
	 * 构造函数
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	function weixin(){
	}

	function __construct(){
		$this->weixin();
	}

	/**
	 * 生成支付代码
	 * @param   array   $order      订单信息
	 * @param   array   $payment    支付方式信息
	 */
	function get_code($order, $payment, $type = ''){

		define('WX_MCHID',  $payment['weixin_mchid']);
		define('WX_APPID',  $payment['weixin_appid']);
		define('WX_SECERT', $payment['weixin_appsecert']);
		define('WX_KEY',    $payment['weixin_key']);

		require_once ROOT_PATH . "includes/modules/payment/WxpayAPI/lib/WxPay.Api.php";
		require_once ROOT_PATH . "includes/modules/payment/WxpayAPI/WxPay.NativePay.php";
		require_once ROOT_PATH . 'includes/modules/payment/WxpayAPI/log.php';

		$str_trade_no = WxPayConfig::MCHID . $order['log_id'].getRandChar(6);
		$notify = new NativePay();
		$input  = new WxPayUnifiedOrder();
		$input->SetBody("支付订单");
		$input->SetAttach("iwchina");
		$input->SetOut_trade_no($str_trade_no);
		$input->SetTotal_fee($order['order_amount'] * 100);
		$input->SetTime_start(local_date("YmdHis", $order['add_time']));
		$input->SetTime_expire(local_date("YmdHis", $order['add_time'] + 24 * 3600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url($GLOBALS['ecs']->url() . 'includes/modules/payment/WxpayAPI/notify.php');
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($order['order_sn']);
		$result = $notify->GetPayUrl($input);

		$str_url = $result["code_url"];
		if (!empty($str_url)){
			$pay_url = '<img src="'.$GLOBALS['ecs']->url().'includes/modules/payment/WxpayAPI/qrcode.php?data='.urlencode($str_url).'" style="width:150px;height:150px;">
			<script type="text/javascript">
			$(function(){
				setInterval(function(){
					checkOrder();
				}, 3000);
			});
			function checkOrder(){
				var logid = \''.intval($order['log_id']).'\';
				$.get(\'flow.php\', {step:\'check_order\', \'log_id\':logid}, function(data){
					if ($.trim(data) == \'ok\'){
						layer.msg(\'支付成功\', {time:1000}, function(){
							location.href=\'flow.php?step=pay_success&order_sn='.$order['order_sn'].'\';
						});
					}
				});
			}
			</script>';
		}else{
			$pay_url = '已支付或订单已失效，请重新下单支付';
		}
		return $pay_url;
	}
}
?>