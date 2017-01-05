<?php
/**
 * Created by PhpStorm.
 * User: kung
 * Date: 16-12-26
 * Time: 上午10:05
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/HXBB2B/hx_request.class.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function get_serial_number(){
    $micro_time =  microtime_float();
    $micro_arr = explode('.',$micro_time);
    return date('YmdHis').substr($micro_arr[1],0,2);
}


$act = $_REQUEST['act'];
$hxb_obj = new HXB2B();

//签到
if($act == 'qiandao'){
    $serial_number = get_serial_number();
    $result = $hxb_obj->DZ015($serial_number);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000')
        $message = '签到成功';
    else
        $message = '签到失败';
    $sql = "insert into ". $ecs->table('hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ015',".
    "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

//签退
if($act == 'qiantui'){
    $serial_number = get_serial_number();
    $result = $hxb_obj->DZ016($serial_number);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000')
        $message = '签退成功';
    else
        $message = '签退失败';
    $sql = "insert into ". $ecs->table('hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ016',".
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

//本行签约子账号
if($act == 'benqian'){
    $data = array();
    $serial_number = get_serial_number();
    $data['MerAccountNo'] = $_POST['MerAccountNo'];
    $data['AccountName'] = $_POST['AccountName'];
    $data['AccountProp'] = $_POST['AccountProp'];
    $data['EnterNetBankNo'] = $_POST['EnterNetBankNo'];
    $data['LawName'] = $_POST['LawName'];
    $data['CertType'] = $_POST['CertType'];
    $data['CertNo'] = $_POST['CertNo'];
    $data['PersonName'] = $_POST['PersonName'];
    $data['OfficeTel'] = $_POST['OfficeTel'];
    $data['Addr'] = $_POST['Addr'];
    $data['Email'] = $_POST['Email'];
    $data['ZipCode'] = $_POST['ZipCode'];
    $data['NoteFlag'] = $_POST['NoteFlag'];
    $data['NotePhone'] = $_POST['NotePhone'];
    $data['CheckFlag'] = $_POST['CheckFlag'];

    $insert_data = $data;
    $insert_data['serial_number'] = $serial_number;
    $insert_data['created_at'] = date("Y-m-d H:i:s");
    $db->autoExecute('ecs_hxb2b_sign_account',$insert_data,'INSERT');


    $result = $hxb_obj->DZ033($serial_number,$data);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000'){
        $message = '子账号签约成功';
        $bankSerNo = $result->MessageData->DataBody->BankTxSerNo;
        $bankSerNo_arr = (array)$bankSerNo;
        $update_data['BankTxSerNo'] = $bankSerNo_arr[0];
        $update_where['serial_number'] = $serial_number;
        $db->autoExecute('ecs_hxb2b_sign_account',$update_data,'UPDATE',$update_where);
    }
    else
        $message = '子账号签约失败';
    $sql = "insert into ". $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ033',".
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}


//他行签约子账号 todo 新增 bankname1 是什么情况
if($act == 'taqian'){
    $data = array();
    $serial_number = get_serial_number();
    $data['MerAccountNo'] = $_POST['MerAccountNo'];
    $data['AccountName'] = $_POST['AccountName'];
    $data['AccountProp'] = $_POST['AccountProp'];
    $data['RelatingAcct'] = $_POST['RelatingAcct'];
    $data['RelatingAcctName'] = $_POST['RelatingAcctName'];
    $data['InterBankFlag'] = $_POST['InterBankFlag'];
    $data['RelatingAcctBank'] = $_POST['RelatingAcctBank'];
    $data['RelatingAcctBankAddr'] = $_POST['RelatingAcctBankAddr'];
    $data['RelatingAcctBankCode'] = $_POST['RelatingAcctBankCode'];
    $data['Amt'] = $_POST['Amt'];
    $data['AmtUse'] = $_POST['AmtUse'];
    $data['PersonName'] = $_POST['PersonName'];
    $data['OfficeTel'] = $_POST['OfficeTel'];
    $data['MobileTel'] = $_POST['MobileTel'];
    $data['Addr'] = $_POST['Addr'];
    $data['ZipCode'] = $_POST['ZipCode'];
    $data['LawName'] = $_POST['LawName'];
    $data['LawType'] = $_POST['LawType'];
    $data['LawNo'] = $_POST['LawNo'];
    $data['NoteFlag'] = $_POST['NoteFlag'];
    $data['NotePhone'] = $_POST['NotePhone'];
    $data['EMail'] = $_POST['EMail'];
    $data['CheckFlag'] = $_POST['CheckFlag'];

    $insert_data = $data;
    $insert_data['serial_number'] = $serial_number;
    $insert_data['created_at'] = date("Y-m-d H:i:s");
    $db->autoExecute('ecs_hxb2b_sign_account_other',$insert_data,'INSERT');


    $result = $hxb_obj->DZ035($serial_number,$data);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000'){
        $message = '子账号签约成功';
        $bankSerNo = $result->MessageData->DataBody->BankTxSerNo;
        $bankSerNo_arr = (array)$bankSerNo;
        $update_data['BankTxSerNo'] = $bankSerNo_arr[0];
        $update_where['serial_number'] = $serial_number;
        $db->autoExecute('ecs_hxb2b_sign_account_other',$update_data,'UPDATE',$update_where);

        $accountinfo = $result->MessageData->DataBody->AccountInfo;
        $accountinfo_arr = (array)$accountinfo;
        $accountinfo_arr['serial_number'] = $serial_number;
        $db->autoExecute('ecs_hxb2b_account',$accountinfo_arr,'INSERT');
    }
    else
        $message = '子账号签约失败';
    $sql = "insert into ". $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ035',".
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

//本行入金
if($act == 'benru'){
    $data = array();
    $serial_number = get_serial_number();
    $data['serial_number'] = $serial_number;
    $data['AccountNo'] = $_POST['AccountNo'];
    $data['MerAccountNo'] = $_POST['MerAccountNo'];
    $data['Amt'] = $_POST['Amt'];
    $data['PasswordChar'] = $_POST['PasswordChar'];
    $data['created_at'] = date("Y-m-d H:i:s");
    $db->autoExecute('ecs_hxb2b_golden',$data,'INSERT');
    $result = $hxb_obj->DZ021($serial_number,$data);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000'){
        $message = '子账号入金成功';
        $bankSerNo = $result->MessageData->DataBody->BankTxSerNo;
        $bankSerNo_arr = (array)$bankSerNo;
        $update_data['BankTxSerNo'] = $bankSerNo_arr[0];
        $update_where['serial_number'] = $serial_number;
        $db->autoExecute('ecs_hxb2b_golden',$update_data,'UPDATE',$update_where);
    }
    else
        $message = '子账号入金失败';
    $sql = "insert into ". $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ021',".
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

//他行入金
if($act == 'taru'){
    $data = array();
    $serial_number = get_serial_number();
    $data['serial_number'] = $serial_number;
    $data['AccountNo'] = $_POST['AccountNo'];
    $data['MerAccountNo'] = $_POST['MerAccountNo'];
    $data['Amt'] = $_POST['Amt'];
    $data['InOutStart'] = $_POST['InOutStart'];
    $data['PersonName'] = $_POST['PersonName'];
    $data['AmoutDate'] = $_POST['AmoutDate'];
    $data['BankName'] = $_POST['BankName'];
    $data['OutAccount'] = $_POST['OutAccount'];
    $data['created_at'] = date("Y-m-d H:i:s");
    $db->autoExecute('ecs_hxb2b_golden',$data,'INSERT');
    $result = $hxb_obj->DZ022($serial_number,$data);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000'){
        $message = '子账号入金登记成功';
        $bankSerNo = $result->MessageData->DataBody->BankTxSerNo;
        $bankSerNo_arr = (array)$bankSerNo;
        $update_data['BankTxSerNo'] = $bankSerNo_arr[0];
        $update_where['serial_number'] = $serial_number;
        $db->autoExecute('ecs_hxb2b_golden',$update_data,'UPDATE',$update_where);
    }
    else
        $message = '子账号入金登记失败';
    $sql = "insert into ". $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ022',".
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

//出金
if($act == 'chujin'){
    $data = array();
    $serial_number = get_serial_number();
    $data['serial_number'] = $serial_number;
    $data['AccountNo'] = $_POST['AccountNo'];
    $data['MerAccountNo'] = $_POST['MerAccountNo'];
    $data['Amt'] = $_POST['Amt'];
    $data['channelType'] = $_POST['channelType'];
    $data['created_at'] = date("Y-m-d H:i:s");
    $db->autoExecute('ecs_hxb2b_dftt',$data,'INSERT');
    $result = $hxb_obj->DZ017($serial_number,$data);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000'){
        $message = '子账号出金成功';
        $bankSerNo = $result->MessageData->DataBody->BankTxSerNo;
        $bankSerNo_arr = (array)$bankSerNo;
        $update_data['BankTxSerNo'] = $bankSerNo_arr[0];
        $update_where['serial_number'] = $serial_number;
        $db->autoExecute('ecs_hxb2b_dftt',$update_data,'UPDATE',$update_where);
    }
    else
        $message = '子账号出金失败';
    $sql = "insert into ". $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ017',".
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

//出金审核发送 todo 该功能应该嵌入到后台功能中
if($act ==  'shenhe'){
    $id = intval($_GET['id']);
    $sql = "select * from ".$ecs->table('hxb2b_chujin')." where id = {$id}";
    $chujin = $db->getOne($sql);
    $data = array();
    $serial_number = get_serial_number();
    $data['serial_number'] = $serial_number;
    $data['BankTxSerNo'] = $chujin['BankTxSerNo'];
    $data['AccountNo'] = $chujin['AccountNo'];
    $data['MerAccountNo'] = $chujin['MerAccountNo'];
    $data['Amt'] = $chujin['Amt'];
    $data['channelType'] = $chujin['channelType'];
    $data['Result'] = intval($_GET['result']);
    $result = $hxb_obj->DZ007($serial_number,$data);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000'){
        $message = '操作出金成功';
        $bankSerNo = $result->MessageData->DataBody->BankTxSerNo;
        $bankSerNo_arr = (array)$bankSerNo;
        $update_data['BankTxSerNo2'] = $bankSerNo_arr[0];
        $update_data['serial_number'] = $serial_number;
        $update_data['Result'] = intval($_GET['result']);
        $update_where['id'] = $id;
        $db->autoExecute('ecs_hxb2b_chujin',$update_data,'UPDATE',$update_where);
    }
    else
        $message = '操作出金失败';
    $sql = "insert into ". $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ007',".
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

//交易商解约
if($act == 'surrender'){
    $data = array();
    $serial_number = get_serial_number();
    $data['serial_number'] = $serial_number;
    $data['AccountNo'] = $_POST['AccountNo'];
    $data['MerAccountNo'] = $_POST['MerAccountNo'];
    $data['created_at'] = date("Y-m-d H:i:s");
    $db->autoExecute('ecs_hxb2b_surrender',$data,'INSERT');
    $result = $hxb_obj->DZ012($serial_number,$data);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if($code_res === '0000'){
        $message = '交易商解约成功';
        $bankSerNo = $result->MessageData->DataBody->BankTxSerNo;
        $bankSerNo_arr = (array)$bankSerNo;
        $update_data['BankTxSerNo'] = $bankSerNo_arr[0];
        $update_where['serial_number'] = $serial_number;
        $db->autoExecute('ecs_hxb2b_surrender',$update_data,'UPDATE',$update_where);
    }
    else
        $message = '交易商解约失败';
    $sql = "insert into ". $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ012',".
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

//清算账目 todo 如果当天没有发生交易额如何处理
if($act == 'qingsuan'){
    $batch = intval($_GET['batch']);
    $data_arr = $data = array();
    $serial_number = get_serial_number();
    $start_time = strtotime(date('Y-m-d') . ' 00:00:00');
    $end_time = strtotime(date('Y-m-d') . ' 23:59:59');
    $sql = "select eha.MerAccountNo,eha.AccountNo,epl.order_amount from ecs_order_info as eoi right JOIN ecs_hxb2b_account as eha on eha.MerAccountNo = eoi.user_id" .
        "  right JOIN ecs_pay_log as epl on eoi.order_id = epl.order_id where eoi.pay_id = 4 and epl.is_paid=1" .
        " and eoi.pay_time > {$start_time} and eoi.pay_time < {$end_time}";
    $account_info = $db->getAll($sql);
    if(!empty($account_info)) {
        if (is_array($account_info)) {
            foreach ($account_info as $account_val) {
                $data['AccountNo'] = $account_val['AccountNo'];
                $data['MerAccountNo'] = $account_val['MerAccountNo'];
                $data['Type'] = '01';
                $data['Amt'] = $account_val['order_amount'];
                $data['Flag'] = 1;
                $data['Remark'] = '';
                $data_arr[] = $data;
            }
        }
    }

    $start_time = date('Y-m-d') . ' 00:00:00';
    $end_time = date('Y-m-d') . ' 23:59:59';
    $sql = "select eha.MerAccountNo,eha.AccountNo,sml.supplier_money from ecs_supplier_money_log as sml RIGHT JOIN".
        " ecs_supplier as es on sml.supplier_id = es.supplier_id right JOIN ecs_hxb2b_account as eha".
        " on eha.MerAccountNo = es.user_id where sml.created_at > '{$start_time}' and sml.created_at < '{$end_time}'".
        " and sml.status = 1";


    $account_info = $db->getAll($sql);
    if(!empty($account_info)) {
        if (is_array($account_info)) {
            foreach ($account_info as $account_val) {
                $data['AccountNo'] = $account_val['AccountNo'];
                $data['MerAccountNo'] = $account_val['MerAccountNo'];
                $data['Type'] = '01';
                $data['Amt'] = $account_val['supplier_money'];
                $data['Flag'] = 2;
                $data['Remark'] = '';
                $data_arr[] = $data;
            }
        }
    }

    $sql = "select eha.MerAccountNo,eha.AccountNo,sml.supplier_money from ecs_supplier_money_log as sml RIGHT JOIN".
        " ecs_supplier as es on sml.supplier_id = es.supplier_id right JOIN ecs_hxb2b_account as eha".
        " on eha.MerAccountNo = es.user_id where sml.created_at > '{$start_time}' and sml.created_at < '{$end_time}'".
        " and sml.status = 2";


    $account_info = $db->getAll($sql);
    if(!empty($account_info)) {
        if (is_array($account_info)) {
            foreach ($account_info as $account_val) {
                $data['AccountNo'] = $account_val['AccountNo'];
                $data['MerAccountNo'] = $account_val['MerAccountNo'];
                $data['Type'] = '01';
                $data['Amt'] = $account_val['supplier_money'];
                $data['Flag'] = 1;
                $data['Remark'] = '';
                $data_arr[] = $data;
            }
        }
    }


    $result = $hxb_obj->DZ008($serial_number, 1, $data_arr);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if ($code_res === '0000') {
        $message = '清算成功';
    } else
        $message = '清算失败';
    $accountinfo = $result->MessageData->DataBody->FailedLiquidations;
    $accountinfo = (array)$accountinfo;
    if (!empty($accountinfo)) {
        $workday_obj = $result->MessageData->DataBody->Workday;
        $workday_arr = (array)$workday_obj;
        $workday = $workday_arr[0];
        $batchNo = '1';
        foreach ($accountinfo['FailedLiquidation'] as $accountinfo_val) {
            $accountinfo_val = (array)$accountinfo_val;
            $accountinfo_val['Workday'] = $workday;
            $accountinfo_val['BatchNo'] = $batchNo;
            $accountinfo_val['serial_number'] = $serial_number;
            $db->autoExecute('ecs_hxb2b_fail_liquidation', $accountinfo_val, 'INSERT');
        }

    }
    $sql = "insert into " . $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ008'," .
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);


}

//对账
if($act == 'duizhang'){
    $data_arr = $data = array();
    $serial_number = get_serial_number();
    $sql = "select u.user_money,u.frozen_money,eha.MerAccountNo,eha.AccountNo from ecs_users as u RIGHT JOIN ecs_hxb2b_account as eha on eha.MerAccountNo = u.user_id";
    $user_data = $db->getAll($sql);
    $data_arr = $data = array();
    foreach($user_data as $user_val){
        $data['AccountNo'] = $user_val['AccountNo'];
        $data['MerAccountNo'] = $user_val['MerAccountNo'];
        $data['Amt'] = $user_val['user_money'];
        $data['AmtUse'] = (float)$user_val['user_money'] - (float)$user_val['frozen_money'];
        $data_arr[] = $data;
    }
    $result = $hxb_obj->DZ009($serial_number, 1, $data_arr);
    $code = $result->MessageData->ResHeader->Status->Code;
    $code_arr = (array)$code;
    $code_res = $code_arr[0];
    if ($code_res === '0000') {
        $message = '对账成功';
    } else
        $message = '对账失败';
    $accountinfo = $result->MessageData->DataBody->FailedAccountChecks;
    $accountinfo = (array)$accountinfo;
    if (!empty($accountinfo)) {
        $workday_obj = $result->MessageData->DataBody->Workday;
        $workday_arr = (array)$workday_obj;
        $workday = $workday_arr[0];
        $batchNo = '1';
        foreach ($accountinfo['FailedAccountCheck'] as $accountinfo_val) {
            $accountinfo_val = (array)$accountinfo_val;
            $accountinfo_val['Workday'] = $workday;
            $accountinfo_val['BatchNo'] = $batchNo;
            $accountinfo_val['serial_number'] = $serial_number;
            $db->autoExecute('ecs_hxb2b_fail_accountCheck', $accountinfo_val, 'INSERT');
        }

    }
    $sql = "insert into " . $ecs->table('ecs_hxbb2b_log') . " set serial_number='{$serial_number}',act_code='DZ009'," .
        "`action`='{$message}',code_res='{$code_res}'";
    $db->query($sql);
}

