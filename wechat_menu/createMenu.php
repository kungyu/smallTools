<?php
/**
 * Created by PhpStorm.
 * User: kung
 * Date: 16-7-14
 * Time: 下午2:51
 */

include __DIR__ . '/../vendor/autoload.php';
use EasyWeChat\Foundation\Application;
$options = [
    'debug'     => false,
    'app_id'    => 'wxd421436eddb8fe4d',
    'secret'    => '8216b7fbf5d85d56ba017af620974660',
    'token'     => 'flowerGo',
    'log' => [
        'level' => 'debug',
        'file'  => '/tmp/easywechat.log',
    ],
    // ...
];

$app = new Application($options);
$menu = $app->menu;

$buttons = [
    [
        "type" => "view",
        "name" => "挑选植物",
        "url"  => "http://qzhdl.com/mobile/"
    ],[
        "type" => "view",
        "name" => "花友社区",
        "url"  => "http://wap.webei.cn/1031434551"
    ],
    [
        "name"       => "会员服务",
        "sub_button" => [
            [
                "type" => "view",
                "name" => "养护知识",
                "url"  => "http://qzhdl.com/mobile/article.php?cid=13"
            ],
            [
                "type" => "click",
                "name" => "分享推荐",
                "key"  => "GET_SHARE"
            ],
            [
                "type" => "view",
                "name" => "我的订单",
                "url"  => "http://qzhdl.com/mobile/user.php?act=order_list"
            ],
            [
                "type" => "view",
                "name" => "快递查询",
                "url"  => "http://qzhdl.com/mobile/wuliu.html"
            ],
            [
                "type" => "view",
                "name" => "注册登录",
                "url"  => "http://qzhdl.com/mobile/user.php?act=user_center"
            ]
        ],
    ],
];
$res = $menu->add($buttons);
echo $res;