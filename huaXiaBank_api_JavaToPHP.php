<?php
/**
 * Created by PhpStorm.
 * User: kung
 * Date: 16-12-20
 * Time: 上午11:28
 */
date_default_timezone_set('Asia/Shanghai');
class HXB2B
{
    private $url = 'http://url/dzserver/portal';
    private $MerNum = '100131';
    //签到
    public function DZ015($MerTxSerNo)
    {
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>" . $MerTxSerNo . "</MerTxSerNo><TrnxCode>DZ015</TrnxCode></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }
    public function DZ016($MerTxSerNo)
    {
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>" . $MerTxSerNo . "</MerTxSerNo><TrnxCode>DZ016</TrnxCode></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }
    //同步子账号
    public function DZ020($MerTxSerNo,$user_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ020</TrnxCode><AccountInfos>".
            "<EMail>ohdas@163.com</EMail><CheckFlag>0</CheckFlag></AccountInfo></AccountInfos>".
            "</DataBody>";
        $xml_msg = $xml_base . $xml_body;
//        echo $xml_msg;exit;
        $result = $this->send_xml($xml_msg);
        return $result;
    }
//清算
    public function DZ008($MerTxSerNo){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ008</TrnxCode><Workday>20090522</Workday><BatchNo>1</BatchNo>".
            "<Liquidations><Liquidation><AccountNo>000003</AccountNo><MerAccountNo>000003</MerAccountNo><Type>01</Type><Amt>10.0</Amt>".
            "<Flag>1</Flag><Remark>备注3</Remark></Liquidation></Liquidations></DataBody>";
        $xml_msg = $xml_base . $xml_body;
//        echo $xml_msg;exit;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    public function DZ009($MerTxSerNo){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ009</TrnxCode><Workday>20090522</Workday><BatchNo>5</BatchNo>".
            "<AccountChecks><AccountCheck><AccountNo>000002</AccountNo><MerAccountNo>000002</MerAccountNo><Amt>200.0</Amt>".
            "<AmtUse>100.0</AmtUse><Interests>20.0</Interests></AccountCheck></AccountChecks></DataBody>";
        $xml_msg = $xml_base . $xml_body;
//        echo $xml_msg;exit;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    private function send_xml($xml_msg){
//        $xml_msg = mb_convert_encoding($xml_msg,'gb2312','utf-8');
        $signature_str = $this->sign($xml_msg);
        $content = "<MessageData>" . $xml_msg . "</MessageData>" .
        "<SignData><Signature>$signature_str</Signature><Signature-Algorithm>SHA1withRSA</Signature-Algorithm></SignData>";
//        $content = mb_convert_encoding($content,'gb2312','utf-8');
//echo $content;exit;
        $content_arr = $this->get_base64_content($content);
        $xml_data = '<?xml version="1.0"?><HXBB2B>' . $content_arr['content'] . '</HXBB2B>';
//        $xml_data = mb_convert_encoding($xml_data,'gbk','utf-8');
        $post_data = $this->get_post_data($content_arr['length'], $xml_data);
echo $post_data;//exit;
        $res = $this->post_url($this->url, $post_data);
        $result = $this->exec_res($res);
        return $result;
    }
    private function get_base_req(){
        $date_time = date('YmdHis', time());
        $xml_base = "<Base><Version>1.0</Version><SignFlag>1</SignFlag><Language>GB2312</Language></Base>";
        $xml_header = "<ReqHeader><ClientTime>" . $date_time . "</ClientTime><MerchantNo>".$this->MerNum."</MerchantNo></ReqHeader>";
        return $xml_base.$xml_header;
    }

    function exec_res($res)
    {
        $xml_res = new SimpleXMLElement($res);
        $xml_result = base64_decode($xml_res);
        $xml_uncompress = gzuncompress($xml_result);
var_dump($xml_uncompress);
//        $xml_uncompress = mb_convert_encoding($xml_uncompress,'utf-8','gbk');

        $xml_obj = new SimpleXMLElement("<HXB2B>" . $xml_uncompress . "</HXB2B>");
        return $xml_obj;
    }

    function sign($data)
    {
        $certs = array();
        openssl_pkcs12_read(file_get_contents("123456.pfx"), $certs, "123456"); //其中password为你的证书密码
        if (!$certs) return;
        $signature = '';
        openssl_sign($data, $signature, $certs['pkey']);
        return base64_encode($signature);
    }

    function get_base64_content($content)
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
/*        echo $original_len."-------";
echo $content;*/
        $result_arr = array(
            'content' => $content,
            'length' => $original_len
        );
        return $result_arr;
    }

    function get_post_data($original_len, $xml_data)
    {
        $headers = array(
            'POST /dzserver/test HTTP/1.1',
            "\r\n",
            'User-Agent: Java/1.3.1',
            "\r\n",
            'Host: 223.72.175.139:9080',
            "\r\n",
            'Cookie: JSESSIONID=aIG-RvFXRUUe',
            "\r\n",
            'Accept: text/html, image/gif, image/jpeg, *; q=.2, * /*; q=.2',
            "\r\n",
            'Connection: keep-alive',
            "\r\n",
            'Content-Type: application/x-www-form-urlencoded',
            "\r\n",
            'Content-Length:'
        );

        $headers_str = implode('', $headers);
        $post_data = $headers_str . $original_len . "\r\n" . "\r\n" . $xml_data;
        return $post_data;
    }

    private function post_url($url, $post_data)
    {
        //初始化curl
        $ch = curl_init();
//设置超时
        curl_setopt($ch, CURLOPT_HTTP_VERSION, '1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

//运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}
