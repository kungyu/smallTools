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


    public function get_data(){
        $this->get_content();
        $this->reset_data();
        $result = '';
        $check_result = $this->check_sign();
        if($check_result){
            $this->TrnxCode = $this->xml_obj->TrnxCode;
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
    public function response_data($data){
        $base = $this->get_response_header();
        $result = "<DataBody>".
            "<BankTxSerNo>".$data['BankTxSerNo']."</BankTxSerNo>".
            "<TrnxCode>".$data['TrnxCode']."</TrnxCode>".
            "<MerTxSerNo>".$data['MerTxSerNo']."</MerTxSerNo>".
            "</DataBody>";
        return $this->export_xml($base . $result);
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
        $result['BankTxSerNo'] = $this->xml_obj->DataBody->BankTxSerNo;
        $result['TrnxCode'] = $this->xml_obj->DataBody->TrnxCode;
        foreach($this->xml_obj->DataBody->AccountInfos as $accountInfo){
            $accountInfo_arr['AccountNo'] = $accountInfo->AccountNo;
            $accountInfo_arr['MerAccountNo'] = $accountInfo->MerAccountNo;
            $accountInfo_arr['AccountName'] = $accountInfo->AccountName;
            $accountInfo_arr['AccountProp'] = $accountInfo->AccountProp;
            $accountInfo_arr['Amt'] = $accountInfo->Amt;
            $accountInfo_arr['AmtUse'] = $accountInfo->AmtUse;
            $accountInfo_arr['PersonName'] = $accountInfo->PersonName;
            $accountInfo_arr['OfficeTel'] = $accountInfo->OfficeTel;
            $accountInfo_arr['MobileTel'] = $accountInfo->MobileTel;
            $accountInfo_arr['Addr'] = $accountInfo->Addr;
            $result['accountInfos'][] = $accountInfo_arr;
        }
        return $result;
    }

    private function DZ002(){
        $result = array();
        $result['BankTxSerNo'] = $this->xml_obj->DataBody->BankTxSerNo;
        $result['TrnxCode'] = $this->xml_obj->DataBody->TrnxCode;
        $result['AccountNo'] = $this->xml_obj->DataBody->AccountNo;
        $result['MerAccountNo'] = $this->xml_obj->DataBody->MerAccountNo;
        $result['Amt'] = $this->xml_obj->DataBody->Amt;
        $result['Balance'] = $this->xml_obj->DataBody->Balance;
        $result['BalanceUse'] = $this->xml_obj->DataBody->BalanceUse;
        $result['reject'] = $this->xml_obj->DataBody->reject;
        $result['Result'] = $this->xml_obj->DataBody->Result;
        return $result;
    }

    private function DZ003(){
        $result = array();
        $result['BankTxSerNo'] = $this->xml_obj->DataBody->BankTxSerNo;
        $result['TrnxCode'] = $this->xml_obj->DataBody->TrnxCode;
        $result['AccountNo'] = $this->xml_obj->DataBody->AccountNo;
        $result['MerAccountNo'] = $this->xml_obj->DataBody->MerAccountNo;
        $result['Amt'] = $this->xml_obj->DataBody->Amt;
        $result['Balance'] = $this->xml_obj->DataBody->Balance;
        $result['BalanceUse'] = $this->xml_obj->DataBody->BalanceUse;
        return $result;
    }

    private function DZ004(){
        $result = array();
        $result['BankTxSerNo'] = $this->xml_obj->DataBody->BankTxSerNo;
        $result['TrnxCode'] = $this->xml_obj->DataBody->TrnxCode;
        foreach($this->xml_obj->DataBody->AccountInfos as $accountInfo){
            $accountInfo_arr['AccountNo'] = $accountInfo->AccountNo;
            $accountInfo_arr['MerAccountNo'] = $accountInfo->MerAccountNo;
            $accountInfo_arr['AccountName'] = $accountInfo->AccountName;
            $result['accountInfos'][] = $accountInfo_arr;
        }
        return $result;
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