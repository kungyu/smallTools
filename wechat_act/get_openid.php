<?php
/**
 * Created by PhpStorm.
 * User: kung
 * Date: 16-10-9
 * Time: 上午10:40
 */

/**
 * 获取用户openid
 */
$wxopenid = '';
$code = $_GET['code'];
if(!$code) {
    $baseUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
    $url = CreateOauthUrlForCode($baseUrl);
    header("Location: $url");
}else{
    $code = $_GET['code'];
    $wxopenid = getOpenidFromMp($code);
}

echo $wxopenid;


function CreateOauthUrlForOpenid($code){
    $urlObj["appid"]  = 'wxd421436eddb8fe4d';
    $urlObj["secret"] = '8216b7fbf5d85d56ba017af620974660';
    $urlObj["code"] = $code;
    $urlObj["grant_type"] = "authorization_code";
    $bizString = ToUrlParams($urlObj);
    return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
}



function GetOpenidFromMp($code){
    $url = CreateOauthUrlForOpenid($code);
    //初始化curl
    $ch = curl_init();
    //设置超时
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    //运行curl，结果以jason形式返回
    $res = curl_exec($ch);
    curl_close($ch);
    //取出openid
    $data = json_decode($res,true);
    setcookie('access_token', $data['access_token']);
    $openid = $data['openid'];
    return $openid;
}

function CreateOauthUrlForCode($redirectUrl){
    $urlObj["appid"] = 'wxd421436eddb8fe4d';
    $urlObj["redirect_uri"] = "$redirectUrl";
    $urlObj["response_type"] = "code";
    $urlObj["scope"] = "snsapi_userinfo";
    $urlObj["state"] = "STATE"."#wechat_redirect";
    $bizString = ToUrlParams($urlObj);
    return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
}

function ToUrlParams($urlObj){
    $buff = "";
    foreach ($urlObj as $k => $v){
        if($k != "sign"){
            $buff .= $k . "=" . $v . "&";
        }
    }

    $buff = trim($buff, "&");
    return $buff;
}
