<?php
/**
 * Created by PhpStorm.
 * User: kung
 * Date: 16-10-24
 * Time: 上午10:50
 */
define('IN_ECS', true);


require(dirname(__FILE__) . '/../includes/init.php');

$mobile = $_REQUEST['mobile'];
$sql = "select order_id from ecs_order_info where shipping_status = 5 and ( tel = '{$mobile}' or mobile = '{$mobile}') order by order_id desc limit 1";
$order_id = $GLOBALS['db']->getOne($sql);

$sql = "select action_note from ecs_order_action where order_id = '{$order_id}' and shipping_status = 5";

$order_action = $GLOBALS['db']->getOne($sql);

if(empty($order_action)){
    echo json_encode(array('message'=>'fail','content'=>'未查到物流信息'));
}else{
    $action = explode('|',$order_action);//快递100 物流查询对照表中的英文 eg:shentong|227708550878(发货单号)
    if(count($action) == 2 && is_numeric($action[1])){
        $kuaidi_info = file_get_contents("http://m.kuaidi100.com/query?type={$action[0]}&postid={$action[1]}&id=1&valicode=&temp=0.10318219421469932");
        $info = json_decode($kuaidi_info,true);
        if(isset($info['message']) && $info['message'] == 'ok'){
            echo json_encode(array('message'=>'ok','content'=>$info['data']));
        }else{
            echo json_encode(array('message'=>'fail','content'=>'未查到物流信息'));
        }
    }else{
        echo json_encode(array('message'=>'fail','content'=>'未查到物流信息'));
    }
}