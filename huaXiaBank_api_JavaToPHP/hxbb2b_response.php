<?php
/**
 * Created by PhpStorm.
 * User: kung
 * Date: 16-12-27
 * Time: 下午3:59
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/HXBB2B/hx_respone.class.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

$HXB2B = new HX_respone($db);
$HXB2B ->get_data();
$HXB2B->response_data();