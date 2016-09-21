<?php
define('IWCHINA', true);

require_once "../../../init.php";

require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'includes/lib_payment.php';
require_once ROOT_PATH . 'includes/modules/payment/WxpayAPI/lib/WxPay.Api.php';
require_once ROOT_PATH . 'includes/modules/payment/WxpayAPI/lib/WxPay.Notify.php';
require_once ROOT_PATH . 'includes/modules/payment/WxpayAPI/log.php';

$payment = get_payment('weixin');
define('WX_MCHID',  $payment['weixin_mchid']);
define('WX_APPID',  $payment['weixin_appid']);
define('WX_SECERT', $payment['weixin_appsecert']);
define('WX_KEY',    $payment['weixin_key']);

ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

//初始化日志
$logHandler= new CLogFileHandler(ROOT_PATH . 'includes/modules/payment/WxpayAPI/logs/'.date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}

		//修改订单状态
		$log_id = intval(str_replace($data['mch_id'], '', $data['out_trade_no']));
		order_paid($log_id, PS_PAYED, '', $data['transaction_id']);
		return true;
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);