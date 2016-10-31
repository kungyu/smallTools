<?php


//define("TOKEN", "flowerGo");
//$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
//
//class wechatCallbackapiTest
//{
//    public function valid()
//    {
//        $echoStr = $_GET["echostr"];
//
//        //valid signature , option
//        if($this->checkSignature()){
//            echo $echoStr;
//            exit;
//        }
//    }
//
//    public function responseMsg()
//    {
//        //get post data, May be due to the different environments
//        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
//
//        //extract post data
//        if (!empty($postStr)){
//            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
//               the best way is to check the validity of xml by yourself */
//            libxml_disable_entity_loader(true);
//            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
//            $fromUsername = $postObj->FromUserName;
//            $toUsername = $postObj->ToUserName;
//            $keyword = trim($postObj->Content);
//            $time = time();
//            $textTpl = "<xml>
//							<ToUserName><![CDATA[%s]]></ToUserName>
//							<FromUserName><![CDATA[%s]]></FromUserName>
//							<CreateTime>%s</CreateTime>
//							<MsgType><![CDATA[%s]]></MsgType>
//							<Content><![CDATA[%s]]></Content>
//							<FuncFlag>0</FuncFlag>
//							</xml>";
//            if(!empty( $keyword ))
//            {
//                $msgType = "text";
//                $contentStr = "Welcome to wechat world!";
//                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
//                echo $resultStr;
//            }else{
//                echo "Input something...";
//            }
//
//        }else {
//            echo "";
//            exit;
//        }
//    }
//
//    private function checkSignature()
//    {
//        // you must define TOKEN by yourself
//        if (!defined("TOKEN")) {
//            throw new Exception('TOKEN is not defined!');
//        }
//
//        $signature = $_GET["signature"];
//        $timestamp = $_GET["timestamp"];
//        $nonce = $_GET["nonce"];
//
//        $token = TOKEN;
//        $tmpArr = array($token, $timestamp, $nonce);
//        // use SORT_STRING rule
//        sort($tmpArr, SORT_STRING);
//        $tmpStr = implode( $tmpArr );
//        $tmpStr = sha1( $tmpStr );
//
//        if( $tmpStr == $signature ){
//            return true;
//        }else{
//            return false;
//        }
//    }
//}
//exit;




define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

include __DIR__ . '/../vendor/autoload.php';
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Transfer;

$options = [
    'debug'     => false,
    'app_id'    => 'wxd421436eddb8fe4d',
    'secret'    => '8216b7fbf5d85d56ba017af620974660',
    'token'     => 'flowerGo',
    // ...
];

$app = new Application($options);
/*$accessToken = $app->access_token;
$token = $accessToken->getToken();*/


$server = $app->server;
$user = $app->user;
$qrcode = $app->qrcode;
$reply = $app->reply;
$temporary = $app->material_temporary;
$server->setMessageHandler(function ($message) use($qrcode,$user,$temporary) {
    switch ($message->MsgType) {
        case 'event':
            # 事件消息...
            switch ($message->Event) {
                case 'subscribe':
                    if(isset($message->EventKey) && isset($message->Ticket)){
                        $qrscene = $message->EventKey;
                        $recom_user_id = get_recom_user_id($qrscene);
                        log_recommend_data($message->FromUserName,$qrscene);
                        $relation = make_relation($message->FromUserName,$recom_user_id);
                        if(!$relation)
                            file_put_contents('wechat.txt',"make recommend relation fail:".$message->FromUserName."|".$recom_user_id."\r\n",FILE_APPEND);
                        else{
                            $affiliate  = unserialize($GLOBALS['_CFG']['affiliate']);
                            if (isset($affiliate['on']) && $affiliate['on'] == 1
                                && $affiliate['config']['level_subscribe_all'] > 0
                                && $recom_user_id != '')
                                log_account_change($recom_user_id, 0,
                                    0, 0, $affiliate['config']['level_subscribe_all'], sprintf($GLOBALS['_LANG']['subscribe_affiliate'], $message->FromUserName));

                        }
                    }
                    return new Text(['content' => 'Hey，终于等到你。在这个钢筋水泥的都市中，FlowerGo愿用最茁壮的绿植，最茂盛的生命，来为你装饰一个安逸的午后。

<a href="http://qzhdl.com">领一盆绿植回家</a>']);
                    break;
                case 'CLICK':
                    if($message->EventKey == 'GET_SHARE'){
                        $openid = $message->FromUserName;
                        $user_id = get_userid_byOpenid($openid);
                        if($user_id) {
                            $poster_id = 161; //todo 广告位置id 根据线上实际id更改
                            $img_base = get_poster_url($poster_id);

                            $img_base = __DIR__ . '/images/qrcode/poster.jpg';
                            $qrcode_dir = __DIR__ . '/images/qrcode/' . $user_id . '_qrcode.jpg';
                            $qrcode_user = __DIR__ . '/images/qrcode/' . $user_id . '.jpg';
                            $header_user = __DIR__ . '/images/qrcode/header_' . $user_id . '.jpg';

                            /*if (!is_file($qrcode_dir)) {*/
                            $result = $qrcode->forever($user_id);
                            $ticket = $result->ticket;
                            $qrcode_url = $qrcode->url($ticket);
                            $qrcode_content = file_get_contents($qrcode_url);
                            file_put_contents($qrcode_user,$qrcode_content);
                            $img_base_source = imagecreatefromjpeg($img_base);
                            $qrcode_content = imagecreatefromjpeg($qrcode_user); //二维码

                            $wx_header = $GLOBALS['db']->getOne("select wx_header from ecs_users where wxopenid = '{$openid}'");

                            if(!empty($wx_header)){
                                file_put_contents($header_user,get_header_content($wx_header));

                                $header_content = imagecreatefromjpeg($header_user);

//                                imagecopyresized($qrcode_content,$header_content,120,120,0,0,170,170,640,640);
                                include ROOT_PATH."/plugins/phpqrcode/qrlib.php";

                                $QR_width = imagesx($qrcode_content);//二维码图片宽度
                                $QR_height = imagesy($qrcode_content);//二维码图片高度
                                $logo_width = imagesx($header_content);//logo图片宽度
                                $logo_height = imagesy($header_content);//logo图片高度
                                $logo_qr_width = $QR_width / 3;
                                $scale = $logo_width/$logo_qr_width;
                                $logo_qr_height = $logo_height/$scale;
                                $from_width = ($QR_width - $logo_qr_width) / 2;
                                //重新组合图片并调整大小
                                imagecopyresampled($qrcode_content, $header_content, $from_width, $from_width, 0, 0, $logo_qr_width,
                                    $logo_qr_height, $logo_width, $logo_height);
                            }

                            $position_arr = get_qrcode_position($poster_id);
                            if(!empty($position_arr))
                                imagecopyresized($img_base_source,$qrcode_content,265,720,0,0,250,250,430,430);
                            imagejpeg($img_base_source,$qrcode_dir,90);
                            /*}*/

//                        $result = $temporary->uploadImage($qrcode_dir);
                            $result = $temporary->uploadImage($qrcode_dir);

                            return new Image(['media_id' => $result->media_id]);
                        }else{
                            return new Text(['content'=>'请点击进FlowerGo，登录后回来获取分享二维码']);
                        }
                        break;
                    }
                    break;
                default:
                    # code...
                    break;
            }
            break;
        case 'text':
            # 文字消息...
            $openid = $message->FromUserName;
            $date = date('Y-m-d',time());

            switch($message->Content){
                case '二维码':
                    $openid = $message->FromUserName;
                    $user_id = get_userid_byOpenid($openid);
                    if($user_id) {
                        $poster_id = 161; //todo 广告位置id 根据线上实际id更改
                        $img_base = get_poster_url($poster_id);

                        $img_base = __DIR__ . '/images/qrcode/poster.jpg';
                        $qrcode_dir = __DIR__ . '/images/qrcode/' . $user_id . '_qrcode.jpg';
                        $qrcode_user = __DIR__ . '/images/qrcode/' . $user_id . '.jpg';
                        $header_user = __DIR__ . '/images/qrcode/header_' . $user_id . '.jpg';

                        /*if (!is_file($qrcode_dir)) {*/
                            $result = $qrcode->forever($user_id);
                            $ticket = $result->ticket;
                            $qrcode_url = $qrcode->url($ticket);
                            $qrcode_content = file_get_contents($qrcode_url);
                            file_put_contents($qrcode_user,$qrcode_content);
                            $img_base_source = imagecreatefromjpeg($img_base);
                            $qrcode_content = imagecreatefromjpeg($qrcode_user); //二维码

                            $wx_header = $GLOBALS['db']->getOne("select wx_header from ecs_users where wxopenid = '{$openid}'");

                            if(!empty($wx_header)){
                                file_put_contents($header_user,get_header_content($wx_header));

                                $header_content = imagecreatefromjpeg($header_user);

//                                imagecopyresized($qrcode_content,$header_content,120,120,0,0,170,170,640,640);
                                include ROOT_PATH."/plugins/phpqrcode/qrlib.php";

                                $QR_width = imagesx($qrcode_content);//二维码图片宽度
                                $QR_height = imagesy($qrcode_content);//二维码图片高度
                                $logo_width = imagesx($header_content);//logo图片宽度
                                $logo_height = imagesy($header_content);//logo图片高度
                                $logo_qr_width = $QR_width / 3;
                                $scale = $logo_width/$logo_qr_width;
                                $logo_qr_height = $logo_height/$scale;
                                $from_width = ($QR_width - $logo_qr_width) / 2;
                                //重新组合图片并调整大小
                                imagecopyresampled($qrcode_content, $header_content, $from_width, $from_width, 0, 0, $logo_qr_width,
                                    $logo_qr_height, $logo_width, $logo_height);
                            }

                            $position_arr = get_qrcode_position($poster_id);
                            if(!empty($position_arr))
                                imagecopyresized($img_base_source,$qrcode_content,265,720,0,0,250,250,430,430);
                            imagejpeg($img_base_source,$qrcode_dir,90);
                        /*}*/

//                        $result = $temporary->uploadImage($qrcode_dir);
                        $result = $temporary->uploadImage($qrcode_dir);

                        return new Image(['media_id' => $result->media_id]);
                    }else{
                        return new Text(['content'=>'请点击进FlowerGo，登录后回来获取分享二维码']);
                    }
                    break;
                default :
                    /*if(is_has($openid,$date)){
                        return false;
                    }else
                        log_first_msg($openid,$date);
                    return new Text(['content'=>"请稍等，客服马上就来。您可以先描述一下问题，客服看到后会为您解答。
或致电4006-536-082。"]);*/
                    return new \EasyWeChat\Message\Transfer();

            }
            break;
        case 'image':
            # 图片消息...
            break;
        case 'voice':
            # 语音消息...
            break;
        case 'video':
            # 视频消息...
            break;
        case 'location':
            # 坐标消息...
            break;
        case 'link':
            # 链接消息...
            break;
        // ... 其它消息
        default:
            # code...
            break;
    }
    // ...
});

$server->serve()->send();

function get_header_content($url=''){
    $res = '';
    if(!empty($url)){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $res = curl_exec($ch);
        curl_close($ch);
    }
    return $res;
}

/**
 * @param $openid 微信openid
 * @return mixed
 */
function get_userid_byOpenid($openid){
    $sql = "select user_id from ecs_users WHERE wxopenid = '{$openid}'";
    $res = $GLOBALS['db']->getOne($sql);
    return $res;
}


function get_recom_user_id($qrscene){
    $result = 0;
    $result_arr = explode('_',$qrscene);
    if(count($result_arr) == 2){
        $result = intval($result_arr[1]);
    }
    return $result;
}

/**
 * 建立推荐人
 * @param $openid 微信openid
 * @param $user_id 推荐人id
 * @return bool
 */
function make_relation($openid,$user_id){
    $res = false;
    $check_sql = "select id from ecs_relation_recommend WHERE wxopenid = '{$openid}'";
    $check_res = $GLOBALS['db']->getRow($check_sql);
    if(empty($check_res)){
        $insert_sql = "insert into ecs_relation_recommend (wxopenid,recom_id) VALUE ('{$openid}','{$user_id}')";
        $result = $GLOBALS['db']->query($insert_sql);
        if($result)
            $res = true;
    }
    return $res;
}

function log_recommend_data($openid='',$qrscene=''){
    if($openid !='' && !$qrscene != ''){
        $sql = "insert into ecs_recommend_subscribe_log (wxopenid,qrscene) VALUES ('{$openid}','{$qrscene}')";
        $GLOBALS['db']->query($sql);
    }
}

/**
 * 检测当天是否已经有消息记录
 * @param $openid
 * @param $date
 * @return bool
 */
function is_has($openid,$date){
    $result = false;
    $sql ="select id from ecs_msg_first_log where wxopenid='{$openid}' and msg_date = '{$date}'";
    if($GLOBALS['db']->getOne($sql))
        $result = true;
    return $result;
}

/**
 * 记录当天第一条消息记录
 * @param $openid
 * @param $date
 */
function log_first_msg($openid,$date){
    $sql = "insert into ecs_msg_first_log SET wxopenid = '{$openid}',msg_date = '{$date}'";
    $GLOBALS['db']->query($sql);
}

/**
 * 获取微信海报图片
 * @param $class_id
 * @return string
 */
function get_poster_url($class_id){
    $pic_url = '';
    if($class_id){
        $sql = "select ad_code from ecs_ad where position_id = {$class_id} order by ad_id desc limit 1";
        $pic_name = $GLOBALS['db']->getOne($sql);
        if(!empty($pic_name))
            $pic_url = ROOT_PATH."data/afficheimg/".$pic_name;
    }
    return $pic_url;
}

/**
 * 获取二维码在海报中的相对位置
 * @param $class_id
 * @return array
 */
function get_qrcode_position($class_id){
    $result = array();
    $class_id = intval($class_id);
    if(!empty($class_id)){
        $sql = "select ad_width,ad_height from ecs_ad_position where position_id = {$class_id}";
        $res = $GLOBALS['db']->getRow($sql);
        if(!empty($res))
            $result = $res;
    }
    return $result;
}