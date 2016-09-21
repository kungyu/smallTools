<?php

define('IWCHINA', true);
require_once "../../../../phone/includes/init.php";
ini_set('date.timezone','Asia/Shanghai');

require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'includes/lib_payment.php';

$payment = get_payment('weixinjs');

define('WX_MCHID',  $payment['weixin_mchid']);
define('WX_APPID',  $payment['weixin_appid']);
define('WX_SECERT', $payment['weixin_appsecert']);
define('WX_KEY',    $payment['weixin_key']);

require_once ROOT_PATH . 'includes/modules/payment/WxpayAPI/lib/WxPay.Api.php';
require_once ROOT_PATH . 'includes/modules/payment/WxpayAPI/WxPay.JsApiPay.php';
require_once ROOT_PATH . 'includes/modules/payment/WxpayAPI/log.php';

//初始化日志
$logHandler= new CLogFileHandler(ROOT_PATH . 'includes/modules/payment/WxpayAPI/logs/'.date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//①、获取用户openid
$tools  = new JsApiPay();

$sql ="select wxopenid from ".$ecs->table('users')." where user_id = '".intval($_SESSION['user_id'])."'";
$openId = $db->getOne($sql);

if(empty($openId)){
	$openId = $tools->GetOpenid();
}


$int_orderid     = intval($_GET['order_id']);
$order           = $db->getRow('SELECT order_id, order_sn, add_time, order_amount FROM '.$ecs->table('order_info')." WHERE order_id = '$int_orderid'");
$order['log_id'] = $db->getOne('SELECT log_id FROM '.$ecs->table('pay_log')." WHERE order_id = '".intval($order['order_id'])."'");

//②、统一下单
$str_trade_no = WxPayConfig::MCHID . $order['log_id'];
$input = new WxPayUnifiedOrder();
$input->SetBody("支付订单");
$input->SetAttach("iwchina");
$input->SetOut_trade_no($str_trade_no);
$input->SetTotal_fee($order['order_amount'] * 100);
$input->SetTime_start(date("YmdHis", $order['add_time']));
$input->SetTime_expire(date("YmdHis", $order['add_time'] + 24 * 3600));
$input->SetGoods_tag("test");
$input->SetNotify_url(str_replace(CUR_PATH.'/', '', $GLOBALS['ecs']->url()) . 'notify.php');
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
//$editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>

<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/> 
	<title>找花网手机端</title>
	<script src="/mobile/script/js/jquery-1.8.3.min.js"></script>
	<script src="/mobile/script/js/layer/layer.js"></script>
	<script type="text/javascript">
	//调用微信JS api 支付
	var order_id = <?php echo $int_orderid; ?>;
	function jsApiCall(){
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				var msg = res.err_msg
				layer.msg(res.err_msg.indexOf('ok') > 0 ? '支付成功' : '支付失败', {time:2000}, function(){
					location.href = '/mobile/flow.php?step=iwchina&sn='+order_id;
				});
			}
		);
	}
	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
			if( document.addEventListener ){
				document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			}else if (document.attachEvent){
				document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
				document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			}
		}else{
			jsApiCall();
		}
	};
	
	</script>
</head>
<body>
</body>
</html>