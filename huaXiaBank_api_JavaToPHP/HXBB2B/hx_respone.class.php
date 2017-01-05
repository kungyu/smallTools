<?php
/**
 * Created by PhpStorm.
 * User: kung
 * Date: 16-12-22
 * Time: 上午11:08
 */

class HX_respone {

    private $data;
    private $xml_obj;
    private $xml_data;
    private $TrnxCode;
    private $db;
    private $serial_number;
    private $bankTxSerNo;

    public function __construct($db)
    {
        $this->db = $db;
        $this->serial_number = $this->get_serial_number();
    }
    

    public function get_data(){
        $this->get_content();
        $this->reset_data();
        $result = '';
        $check_result = $this->check_sign();
        if($check_result){
            $trcode = $this->xml_obj->TrnxCode;
            $trcode = (array)$trcode;
            $this->TrnxCode = $trcode[0];
            $bankTxSerNo = $this->xml_obj->DataBody->BankTxSerNo;
            $bankTxSerNo = (array)$bankTxSerNo;
            $this->bankTxSerNo = $bankTxSerNo[0];
            if($this->TrnxCode == 'DZ001')
                $result = $this->DZ001();
            if($this->TrnxCode == 'DZ002')
                $result = $this->DZ002();
            if($this->TrnxCode == 'DZ003')
                $result = $this->DZ003();
            if($this->TrnxCode == 'DZ004')
                $result = $this->DZ004();
        }
        return $result;
    }


    /**
     * @param $data array('BankTxSerNo'=>银行流水号，'TrnxCode'=>交易代码, 'MerTxSerNo'=>交易市场流水号)
     * @return string
     */
    public function response_data(){
        $base = $this->get_response_header();
        $result = "<DataBody>".
            "<BankTxSerNo>". $this->bankTxSerNo ."</BankTxSerNo>".
            "<TrnxCode>". $this->TrnxCode ."</TrnxCode>".
            "<MerTxSerNo>". $this->serial_number ."</MerTxSerNo>".
            "</DataBody>";
        return $this->export_xml($base . $result);
    }

    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    private function get_serial_number(){
        $micro_time =  $this->microtime_float();
        $micro_arr = explode('.',$micro_time);
        return date('YmdHis').substr($micro_arr[1],0,2);
    }

    private function get_content(){
        $data = file_get_contents("php://input");
        $this->data = $data;
    }

    private function reset_data(){
        $data = substr($this->data,stripos($this->data,'<HXBB2B>'));
        $xml_res = new SimpleXMLElement($data);
        $xml_result = base64_decode($xml_res);
        $xml_uncompress = gzuncompress($xml_result);
        var_dump($xml_uncompress);
        $this->xml_data = $xml_uncompress;
        $xml_obj = new SimpleXMLElement("<HXB2B>" . $xml_uncompress . "</HXB2B>");
        $this->xml_obj = $xml_obj;
    }

    private function check_sign(){
        $signature = $this->xml_obj->SignData->Signature;
        $start_ipos = stripos($this->xml_data,'<DataBody>') + 10;
        $end_ipos = stripos($this->xml_data,'</DataBody>') - 10;
        $data_body = trim(substr($this->xml_data,$start_ipos,$end_ipos));
        return $this->verify($data_body,$signature);
    }

    private function verify($data, $signature) {
        $certs = array();
        openssl_pkcs12_read(file_get_contents("123456.pfx"), $certs,  "123456");
        if(!$certs) return ;
        $result = (bool) openssl_verify($data, $signature, $certs['cert']); //openssl_verify验签成功返回1，失败0，错误返回-1
        return $result;
    }

    private function DZ001(){
        $result = array();
        $bankTxSerNo = $this->xml_obj->DataBody->BankTxSerNo;
        $bankTxSerNo = (array)$bankTxSerNo;
//        $result['BankTxSerNo'] = $bankTxSerNo[0];
        $trnxCode = $this->xml_obj->DataBody->TrnxCode;
        $trnxCode = (array)$trnxCode;
//        $result['TrnxCode'] = $trnxCode[0];
        $accountInfos = $this->xml_obj->DataBody->AccountInfos;
        $accountInfos = (array)$accountInfos;
        foreach($accountInfos['AccountInfo'] as $accountInfo){
            $accountInfo = (array)$accountInfo;
            $accountInfo['BankTxSerNo'] = $bankTxSerNo[0];
            $accountInfo['TrnxCode'] = $trnxCode[0];
            $accountInfo['serial_number'] = $this->serial_number;
            $result[] = $accountInfo;
        }
        foreach($result as $result_val){
            $this->db->autoExecute('ecs_hxb2b_DZ001',$result_val,"INSERT");
            $sql = "select AccountNo,MerAccountNo from ecs_hxb2b_account WHERE MerAccountNo = '{$result_val['MerAccountNo']}'";
            $account_check = $this->db->getRow($sql);
            if(!empty($account_check)){
                $sql = "update ecs_hxb2b_account set AccountNo = '{$result_val['AccountNo']}' WHERE MerAccountNo = '{$result_val['MerAccountNo']}'";
            }else{
                $sql = "insert into ecs_hxb2b_account set AccountNo = '{$result_val['AccountNo']}',MerAccountNo = '{$result_val['MerAccountNo']}'";
            }
            $this->db->query($sql);
            $user_money = $result_val['Amt'];
            $frozen_money = (float)$result_val['Amt'] - (float)$result_val['AmtUse'];
            $sql = "update ecs_users set user_money = '{$user_money}',frozen_money = '{$frozen_money}' where user_id = '{$result_val['MerAccountNo']}'";
            $this->db->query($sql);
        }
        return true;
    }

    private function DZ002(){
        $dataBody = $this->xml_obj->DataBody;
        $dataBody = (array)$dataBody;

        $dataBody['serial_number'] = $this->serial_number;
        $this->db->autoExecute('ecs_hxb2b_DZ002',$dataBody,"INSERT");
        $user_money = $dataBody['Balance'];
        $frozen_money = (float)$dataBody['Balance'] - (float)$dataBody['BalanceUse'];
        $sql = "update ecs_users set user_money = '{$user_money}', frozen_money = '{$frozen_money}' where user_id = '{$dataBody['MerAccountNo']}'";
        $this->db->query($sql);
        return true;
    }

    private function DZ003(){
        $dataBody = $this->xml_obj->DataBody;
        $dataBody = (array)$dataBody;
        foreach($dataBody as $dataBody_val){
            $dataBody_val['serial_number'] = $this->serial_number;
            $this->db->autoExecute('ecs_hxb2b_chujin',$dataBody_val,"INSERT");
        }
        return true;
    }

    private function DZ004(){
        $result = array();
        $bankTxSerNo = $this->xml_obj->DataBody->BankTxSerNo;
        $bankTxSerNo = (array)$bankTxSerNo;
//        $result['BankTxSerNo'] = $bankTxSerNo[0];
        $trnxCode = $this->xml_obj->DataBody->TrnxCode;
        $trnxCode = (array)$trnxCode;
//        $result['TrnxCode'] = $trnxCode[0];
        $accountInfos = $this->xml_obj->DataBody->AccountInfos;
        $accountInfos = (array)$accountInfos;
        foreach($accountInfos['AccountInfo'] as $accountInfo){
            $accountInfo = (array)$accountInfo;
            $accountInfo['BankTxSerNo'] = $bankTxSerNo[0];
            $accountInfo['TrnxCode'] = $trnxCode[0];
            $accountInfo['serial_number'] = $this->serial_number;
            $result[] = $accountInfo;
        }
        foreach($result as $result_val){
            $this->db->autoExecute('ecs_hxb2b_DZ004',$result_val,"INSERT");
            $sql = "insert into ecs_hxb2b_account_surrender select *,null from ecs_hxb2b_account  where MerAccountNo = '{$result_val['MerAccountNo']}'";
            $this->db->query($sql);
            $sql = "delete from ecs_hxb2b_account  where MerAccountNo = '{$result_val['MerAccountNo']}'";
            $this->db->query($sql);
        }
        return true;
    }

    private function get_response_header(){
        $result = "<Base>".
                    "<Version>1.0</Version>".
                    "<SignFlag>1</SignFlag>".
                    "<Language>GB2312</Language>".
                  "</Base>".
            "<ResHeader>".
            "<ServerTime>".date('YmdHis')."</ServerTime>".
            "<Status>".
            "<Code>0000</Code>".
            "<Message>交易成功</Message>".
            "</Status>".
            "</ResHeader>";
        return $result;
    }

    private function export_xml($xml_msg){
        $signature_str = $this->sign($xml_msg);
        $content = "<MessageData>" . $xml_msg . "</MessageData>" .
            "<SignData><Signature>$signature_str</Signature><Signature-Algorithm>SHA1withRSA</Signature-Algorithm></SignData>";
        $content_arr = $this->get_base64_content($content);
        $xml_data = '<?xml version="1.0"?><HXBB2B>' . $content_arr['content'] . '</HXBB2B>';
        return $xml_data;
    }

    private function sign($data)
    {
        $certs = array();
        openssl_pkcs12_read(file_get_contents("123456.pfx"), $certs, "123456"); //其中password为你的证书密码
        if (!$certs) return;
        $signature = '';
        openssl_sign($data, $signature, $certs['pkey']);
        return base64_encode($signature);
    }

    private function get_base64_content($content)
    {
        $content = $content;
        $original_len = strlen($content);
        $content = gzcompress($content);
        $compress_len = strlen($content);
        $red_len = $original_len - $compress_len;

        $str = '';
        $a = base64_decode("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA");
        $sub_a = substr($a, 0, 1);
        for ($i = 0; $i < $red_len; $i++) {
            $str .= $sub_a;
        }
        $content = $content . $str;
        $content = base64_encode($content) . "";
        $result_arr = array(
            'content' => $content,
            'length' => $original_len
        );
        return $result_arr;
    }
}