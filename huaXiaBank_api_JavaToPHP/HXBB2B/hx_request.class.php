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
    private $url = 'http://223.72.175.139:9080/dzserver/portal';
    private $MerNum = '100131';

    //出金审核结果发送
    /**
     * @author kung </td><td> 出金审核结果发送
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('BankTxSerNo' => 银行流水账号,'AccountNo' => 子账户,'MerAccountNo' => 摊位号,
     * 'Amt' => 总金额'channelType' => 0 快速到账 1 跨行清算'Result' => 0 拒绝 1 通过)
     * @return SimpleXMLElement
     */
    public function DZ007($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ007</TrnxCode>".
            "<BankTxSerNo>".$send_data['BankTxSerNo']."</BankTxSerNo>". //银行流水账号
            "<AccountNo>".$send_data['AccountNo']."</AccountNo>". //子账户
            "<MerAccountNo>".$send_data['MerAccountNo']."</MerAccountNo>".
            "<Amt>".$send_data['Amt']."</Amt>".
            "<channelType>".$send_data['channelType']."</channelType>". //0 快速到账 1 跨行清算
            "<Result>".$send_data['Result']."</Result>". // 0 拒绝 1 通过
            "</DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

//清算

    /**
     * @author kung </td><td width=100>    清算
     * @param $MerTxSerNo 交易市场流水号
     * @param $BatchNo 清算批次
     * @param $send_data array('AccountNo'=> 子账号, 'MerAccountNo' => 交易市场摊位号, 'Type' => 1：正常交易（仅对子账号进行借贷）；02：冻结资金
     * 'Amt' => 金额, 'Flag' => 1 借 2 贷, 'Remark' => 备注)
     * @return SimpleXMLElement
     */
    public function DZ008($MerTxSerNo,$BatchNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ008</TrnxCode><Workday>".date('Ymd')."</Workday>".
            "<BatchNo>".$BatchNo."</BatchNo>".
            "<Liquidations>";
        $loop_data = '';
            foreach($send_data as $val) {
                $loop_data .=  "<Liquidation>" .
                "<AccountNo>".$val['AccountNo']."</AccountNo>" .
                "<MerAccountNo>".$val['MerAccountNo']."</MerAccountNo>" .
                "<Type>".$val['Type']."</Type><Amt>".$val['Amt']."</Amt>" .
                "<Flag>".$val['Flag']."</Flag><Remark>".$val['Remark']."</Remark>" .
                "</Liquidation>";
            }
        $xml_body = $xml_body . $loop_data .  "</Liquidations></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }


//对账
    /**
     * @author kung </td><td> 对账
     * @param $MerTxSerNo 交易市场流水号
     * @param $BatchNo 清算批次
     * @param $send_data array('AccountNo'=> 子账号, 'MerAccountNo' => 摊位号, 'Amt'=> 总金额 ,'AmtUse'=> 可用余额)
     * @return SimpleXMLElement
     */
    public function DZ009($MerTxSerNo,$BatchNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ009</TrnxCode><Workday>".date('Ymd')."</Workday>".
            "<BatchNo>".$BatchNo."</BatchNo>".
            "<AccountChecks>";
        $loop_data = '';
        foreach($send_data as $val) {
            $loop_data .= "<AccountCheck>" .
            "<AccountNo>".$val['AccountNo']."</AccountNo>" .
            "<MerAccountNo>".$val['MerAccountNo']."</MerAccountNo>" .
            "<Amt>".$val['Amt']."</Amt>" .
            "<AmtUse>".$val['AmtUse']."</AmtUse><Interests>0</Interests>" .
            "</AccountCheck>";
        }
        $xml_body = $xml_body . $loop_data . "</AccountChecks></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //同步子账号
    /**
     * @author kung </td><td> 同步子账号
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('AccountNo'=> 子账号, 'MerAccountNo' => 摊位号, 'AccountName'=>账户姓名, 'AccountProp'=> 0 企业 1 个人
     * 'Amt'=> 总金额 ,'AmtUse'=> 可用余额, 'PersonName'=> 联系人, 'OfficeTel'=> 办公电话, 'MobileTel' => 移动电话)
     * @return SimpleXMLElement
     */
    public function DZ010($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ010</TrnxCode>".
            "<AccountInfos>";
        $loop_data = '';
            foreach($send_data as $val) {
                $loop_data .= "<AccountInfo>" .
                "<AccountNo>".$val['AccountNo']."</AccountNo>" . //子账户号 6位
                "<MerAccountNo>".$val['MerAccountNo']."</MerAccountNo>" . //用户id
                "<AccountName>".$val['AccountName']."</AccountName>" .
                "<AccountProp>".$val['AccountProp']."</AccountProp>" . //0 企业 1 个人
                "<Amt>".$val['Amt']."</Amt>" .
                "<AmtUse>".$val['AmtUse']."</AmtUse>" .
                "<PersonName>".$val['PersonName']."</PersonName>" .
                "<OfficeTel>".$val['OfficeTel']."</OfficeTel>" .
                "<MobileTel>".$val['MobileTel']."</MobileTel>" .
                "<Addr>".$val['AccountNo']."</Addr>" .
                "</AccountInfo>";
            }
            //循环体结束
        $xml_body = $xml_body . $loop_data . "</AccountInfos></DataBody>";
        $xml_msg = $xml_base . $xml_body;
//        echo $xml_msg;exit;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //交易商解约
    /**
     * @author kung </td><td>  交易商解约
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('AccountNo' => 子账号, 'MerAccountNo' => 摊位号)
     * @return SimpleXMLElement
     */
    public function DZ012($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ012</TrnxCode>".
            "<AccountNo>".$send_data['AccountNo']."</AccountNo>".
            "<MerAccountNo>".$send_data['MerAccountNo']."</MerAccountNo></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //签到
    /**
     * @author kung </td><td> 签到
     * @param $MerTxSerNo 交易市场流水号
     * @return SimpleXMLElement
     */
    public function DZ015($MerTxSerNo)
    {
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>" . $MerTxSerNo . "</MerTxSerNo><TrnxCode>DZ015</TrnxCode></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //签退
    /**
     * @author kung </td><td> 签退
     * @param $MerTxSerNo 交易市场流水号
     * @return SimpleXMLElement
     */
    public function DZ016($MerTxSerNo)
    {
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>" . $MerTxSerNo . "</MerTxSerNo><TrnxCode>DZ016</TrnxCode></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //交易市场出金
    /**
     * @author kung </td><td> 交易市场出金
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('AccountNo'=> 子账号, 'MerAccountNo' => 摊位号, 'Amt' => 总金额, 'channelType' => 0 快速到账 1 跨行清算)
     * @return SimpleXMLElement
     */
    public function DZ017($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>" . $MerTxSerNo . "</MerTxSerNo><TrnxCode>DZ017</TrnxCode>".
            "<AccountNo>".$send_data['AccountNo']."</AccountNo>". //子账户
            "<MerAccountNo>".$send_data['MerAccountNo']."</MerAccountNo>".
            "<Amt>".$send_data['Amt']."</Amt>".
            "<channelType>".$send_data['channelType']."</channelType>". //0 快速到账 1 跨行清算
        "</DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //出入金明细核对
    /**
     * @author kung </td><td> 出入金明细核对
     * @param $MerTxSerNo 交易市场流水号
     * @param $date 'Y-m-d'
     * @return SimpleXMLElement
     */
    public function DZ018($MerTxSerNo,$date){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>" . $MerTxSerNo . "</MerTxSerNo><TrnxCode>DZ018</TrnxCode>".
            "<Date>".$date."</Date>".
            "</DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //签约子账号
    /**
     * @author kung </td><td> 签约子账号
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('MerAccountNo' => 摊位号, 'AccountName'=>子账号名称, 'AccountProp' => 性质 0 企业 1个人,
     * 'RelatingAcct'=> 关联卡号, 'RelatingAcctName' => 关联账户名, 'InterBankFlag' => 跨行标识：0-本行 1-跨行（★）只能填1,
     * 'RelatingAcctBank' => 绑定出金账户开户行（跨行时需要）,'RelatingAcctBankAddr' => 绑定出金账户开户行地址（跨行时需要）,
     * 'RelatingAcctBankCode' => 绑定出金账户开户行支付系统行号（跨行时需要）,'Amt' => 总金额, 'AmtUse'=> 可用金额,
     * 'PersonName'=>联系人（子账户性质为企业时必须）,'OfficeTel' => 办公电话（子账户性质为企业时必须）,
     * 'MobileTel'=> 移动电话（子账户性质为企业时必须）, 'Addr'=> 联系地址（子账户性质为企业时必须）, 'ZipCode' => 邮编,
     * 'LawName'=>法人姓名（子账户性质为企业时必须）, 'LawType'=> 证件类型1 ? 个人身份证 2 ? 军人证、警官证 3 ? 临时证件 4 ? 户口本 5 ? 护照 6 ? 其他,
     * 'LawNo' => 证件号码,'NoteFlag'=>是否需要短信通知：1-需要，0-不需要,
     * 'NotePhone'=>短信通知手机号码,'EMail'=>电子邮箱, 'CheckFlag'=> 复核标识 1：需要 0:不需要)
     * @return SimpleXMLElement
     */
    public function DZ020($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ020</TrnxCode><AccountInfos>";
        $xml_loop = '';
        foreach($send_data as $val) {
            $xml_loop .= "<AccountInfo><MerAccountNo>".$val['MerAccountNo']."</MerAccountNo>" .
            "<AccountName>".$val['AccountName']."</AccountName><AccountProp>".$val['AccountProp']."</AccountProp>" .
            "<RelatingAcct>".$val['RelatingAcct']."</RelatingAcct><RelatingAcctName>".$val['RelatingAcctName']."</RelatingAcctName>" .
            "<InterBankFlag>".$val['InterBankFlag']."</InterBankFlag><RelatingAcctBank>".$val['RelatingAcctBank']."</RelatingAcctBank>".
                "<RelatingAcctBankAddr>".$val['RelatingAcctBankAddr']."</RelatingAcctBankAddr><RelatingAcctBankCode>".$val['RelatingAcctBankCode']."</RelatingAcctBankCode>" .
            "<Amt>".$val['Amt']."</Amt><AmtUse>".$val['AmtUse']."</AmtUse><PersonName>".$val['PersonName']."</PersonName>" .
            "<OfficeTel>".$val['OfficeTel']."</OfficeTel><MobileTel>".$val['MobileTel']."</MobileTel>" .
            "<Addr>".$val['Addr']."</Addr><ZipCode>".$val['ZipCode']."</ZipCode><LawName>".$val['LawName']."</LawName><LawType>".$val['LawType']."</LawType>" .
            "<LawNo>".$val['LawNo']."</LawNo><NoteFlag>".$val['NoteFlag']."</NoteFlag><NotePhone>".$val['NotePhone']."</NotePhone>" .
            "<EMail>".$val['EMail']."</EMail><CheckFlag>".$val['CheckFlag']."</CheckFlag></AccountInfo>";
        }
        $xml_body = $xml_body .  $xml_loop . "</AccountInfos></DataBody>";
        $xml_msg = $xml_base . $xml_body;
    //        echo $xml_msg;exit;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

//入金申请
    /**
     * @author kung </td><td> 入金申请
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('AccountNo'=> 子账号, 'MerAccountNo'=> 摊位号, 'Amt'=>金额,'PasswordChar'=> 个人卡支付密码)
     * @return SimpleXMLElement
     */
    public function DZ021($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ021</TrnxCode>".
            "<AccountNo>".$send_data['AccountNo']."</AccountNo>". //青岛银行子账户
            "<MerAccountNo>".$send_data['MerAccountNo']."</MerAccountNo><Amt>".$send_data['Amt']."</Amt>".
            "<PasswordChar>".$send_data['PasswordChar']."</PasswordChar></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //入金登记申请
    /**
     * @author kung </td><td> 入金登记申请
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('AccountNo'=> 子账号, 'MerAccountNo'=> 摊位号,'Amt' => 金额, 'InOutStart'=>1 他行现金汇款，2他行转账汇款,
     * 'PersonName' => 汇款人姓名, 'AmoutDate'=>汇款日期,'BankName'=>汇款银行, 'OutAccount'=> 汇款账号)
     * @return SimpleXMLElement
     */
    public function DZ022($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody>".
            "<MerTxSerNo>".$MerTxSerNo."</MerTxSerNo>".
            "<TrnxCode>DZ022</TrnxCode>".
            "<AccountNo>".$send_data['AccountNo']."</AccountNo>".
            "<MerAccountNo>".$send_data['MerAccountNo']."</MerAccountNo>".
            "<Amt>".$send_data['Amt']."</Amt>".
            "<InOutStart>".$send_data['InOutStart']."</InOutStart>". //1 他行现金汇款，2他行转账汇款
            '<PersonName>'.$send_data['PersonName'].'</PersonName>'.
            '<AmoutDate>'.$send_data['AmoutDate'].'</AmoutDate>'. //汇款日期
            '<BankName>'.$send_data['BankName'].'</BankName>'.
            '<OutAccount>'.$send_data['OutAccount'].'</OutAccount>'. //汇款账号
            '</DataBody>';
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //银行子账户余额查询
    /**
     * @author kung </td><td> 银行子账户余额查询
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('AccountNo'=> 子账号, 'MerAccountNo'=> 摊位号)
     * @return SimpleXMLElement
     */
    public function DZ032($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody>".
            "<MerTxSerNo>".$MerTxSerNo."</MerTxSerNo>".
            "<TrnxCode>DZ032</TrnxCode>".
            "<AccountNo>".$send_data['AccountNo']."</AccountNo>".
            "<MerAccountNo>".$send_data['MerAccountNo']."</MerAccountNo>".
            '</DataBody>';
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //新增子账号同步
    /**
     * @author kung </td><td> 新增子账号同步
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('MerAccountNo' => 摊位号, 'AccountName'=>子账号名称, 'AccountProp' => 性质 0 企业 1个人,
     * 'EnterNetBankNo' => 企业客户号,
     * 'LawName'=>法人姓名（子账户性质为企业时必须）, 'CertType'=> 证件类型 1 身份证 4户口 5 护照,
     * 'CertNo' => 证件号码,'PersonName' => 联系人, 'OfficeTel' => 办公电话, 'Addr'=>企业地址, 'Email'=> 邮箱, 'ZipCode'=> 邮编
     * 'NoteFlag'=>是否需要短信通知：1-需要，0-不需要,
     * 'NotePhone'=>短信通知手机号码,'EMail'=>电子邮箱, 'CheckFlag'=> 复核标识 1：需要 0:不需要)
     * @return SimpleXMLElement
     */
    public function DZ033($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ033</TrnxCode>".
            "<AccountInfo><MerAccountNo>".$send_data['MerAccountNo']."</MerAccountNo>".
            "<AccountName>".$send_data['AccountName']."</AccountName><AccountProp>".$send_data['AccountProp']."</AccountProp>".
            "<EnterNetBankNo>".$send_data['EnterNetBankNo']."</EnterNetBankNo>". //企业客户号
            "<LawName>".$send_data['LawName']."</LawName>".
            "<CertType>".$send_data['CertType']."</CertType>". //1 身份证 4户口 5 护照
            "<CertNo>".$send_data['CertNo']."</CertNo>". //证件号码
            "<PersonName>".$send_data['PersonName']."</PersonName>". //联系人
            "<OfficeTel>".$send_data['OfficeTel']."</OfficeTel>".
            "<Addr>".$send_data['Addr']."</Addr>".
            "<Email>".$send_data['Email']."</Email>".
            "<ZipCode>".$send_data['ZipCode']."</ZipCode>".
            "<NoteFlag>".$send_data['NoteFlag']."</NoteFlag>". //1 需要 2 不需要
            "<NotePhone>".$send_data['NotePhone']."</NotePhone>".
            "<CheckFlag>".$send_data['CheckFlag']."</CheckFlag>". //是否需要复核 1 需要 0 不需要
            "</AccountInfo>".
            "</DataBody>";
        $xml_msg = $xml_base . $xml_body;
        //        echo $xml_msg;exit;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //子账户信息查询
    /**
     * @author kung </td><td> 子账户信息查询
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data 摊位号
     * @return SimpleXMLElement
     */
    public function DZ034($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ034</TrnxCode><MerAccountNo>".$send_data."</MerAccountNo></DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    //子账户签约，只用于他行客户和个人
    /**
     * @author kung </td><td> 子账户签约，只用于他行客户和个人
     * @param $MerTxSerNo 交易市场流水号
     * @param $send_data array('MerAccountNo' => 摊位号, 'AccountName'=>子账号名称, 'AccountProp' => 性质 0 企业 1个人,
     * 'RelatingAcct'=> 关联卡号, 'RelatingAcctName' => 关联账户名, 'InterBankFlag' => 跨行标识：0-本行 1-跨行（★）只能填1,
     * 'RelatingAcctBank' => 绑定出金账户开户行（跨行时需要）,'RelatingAcctBankAddr' => 绑定出金账户开户行地址（跨行时需要）,
     * 'RelatingAcctBankCode' => 绑定出金账户开户行支付系统行号（跨行时需要）,'Amt' => 总金额, 'AmtUse'=> 可用金额,
     * 'PersonName'=>联系人（子账户性质为企业时必须）,'OfficeTel' => 办公电话（子账户性质为企业时必须）,
     * 'MobileTel'=> 移动电话（子账户性质为企业时必须）, 'Addr'=> 联系地址（子账户性质为企业时必须）, 'ZipCode' => 邮编,
     * 'LawName'=>法人姓名（子账户性质为企业时必须）, 'LawType'=> 证件类型1 ? 个人身份证 2 ? 军人证、警官证 3 ? 临时证件 4 ? 户口本 5 ? 护照 6 ? 其他,
     * 'LawNo' => 证件号码,'NoteFlag'=>是否需要短信通知：1-需要，0-不需要,
     * 'NotePhone'=>短信通知手机号码,'EMail'=>电子邮箱, 'CheckFlag'=> 复核标识 1：需要 0:不需要)
     * @return SimpleXMLElement
     */
    public function DZ035($MerTxSerNo,$send_data){
        $xml_base = $this->get_base_req();
        $xml_body = "<DataBody><MerTxSerNo>".$MerTxSerNo."</MerTxSerNo><TrnxCode>DZ035</TrnxCode>".
            "<AccountInfo>".
            "<MerAccountNo>".$send_data['MerAccountNo']."</MerAccountNo>".
            "<AccountName>".$send_data['AccountName']."</AccountName>".
            "<AccountProp>".$send_data['AccountProp']."</AccountProp>". //0 企业 1 个人
            "<RelatingAcct>".$send_data['RelatingAcct']."</RelatingAcct>". //关联卡号
            "<RelatingAcctName>".$send_data['RelatingAcctName']."</RelatingAcctName>". //关联账户名
            "<InterBankFlag>".$send_data['InterBankFlag']."</InterBankFlag>". //0 本行 1 跨行
            "<RelatingAcctBank>".$send_data['RelatingAcctBank']."</RelatingAcctBank>".
            "<RelatingAcctBankAddr>".$send_data['RelatingAcctBankAddr']."</RelatingAcctBankAddr>".
            "<RelatingAcctBankCode>".$send_data['RelatingAcctBankCode']."</RelatingAcctBankCode>". //绑定出金账户开户行支付系统行号（跨行时需要）
/*            "<bankname1></bankname1>". //绑定跨行清算出金账户开户行
            "<bankexchno1></bankexchno1>". //绑定出金账户开户行跨行清算支付系统行号*/
            "<Amt>".$send_data['Amt']."</Amt>".
            "<AmtUse>".$send_data['AmtUse']."</AmtUse>".
            "<PersonName>".$send_data['PersonName']."</PersonName>".
            "<OfficeTel>".$send_data['OfficeTel']."</OfficeTel>".
            "<MobileTel>".$send_data['MobileTel']."</MobileTel>".
            "<Addr>".$send_data['Addr']."</Addr>".
            "<ZipCode>".$send_data['ZipCode']."</ZipCode>".
            "<LawName>".$send_data['LawName']."</LawName>".
            "<LawType>".$send_data['LawType']."</LawType>".
            "<LawNo>".$send_data['LawNo']."</LawNo>".
            "<NoteFlag>".$send_data['NoteFlag']."</NoteFlag>". //短信通知
            "<NotePhone>".$send_data['NotePhone']."</NotePhone>".
            "<EMail>".$send_data['EMail']."</EMail>".
            "<CheckFlag>".$send_data['CheckFlag']."</CheckFlag>". //复核 0 不需要 1 需要.
            "</AccountInfo>".
            "</DataBody>";
        $xml_msg = $xml_base . $xml_body;
        $result = $this->send_xml($xml_msg);
        return $result;
    }

    private function send_xml($xml_msg){
        $xml_msg = mb_convert_encoding($xml_msg,'gb2312','utf-8');
        $signature_str = $this->sign($xml_msg);
        $content = "<MessageData>" . $xml_msg . "</MessageData>" .
        "<SignData><Signature>$signature_str</Signature><Signature-Algorithm>SHA1withRSA</Signature-Algorithm></SignData>";
//        $content = mb_convert_encoding($content,'gb2312','utf-8');
//echo $content;exit;
        $content_arr = $this->get_base64_content($content);
        $xml_data = '<?xml version="1.0"?><HXBB2B>' . $content_arr['content'] . '</HXBB2B>';
//        $xml_data = mb_convert_encoding($xml_data,'gbk','utf-8');
        $post_data = $this->get_post_data($content_arr['length'], $xml_data);
//echo $post_data;
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

    private function exec_res($res)
    {
        $xml_res = new SimpleXMLElement($res);
        $xml_result = base64_decode($xml_res);
        $xml_uncompress = gzuncompress($xml_result);
//var_dump($xml_uncompress);
        $xml_uncompress = mb_convert_encoding($xml_uncompress,'utf-8','gbk');

        $xml_obj = new SimpleXMLElement("<HXB2B>" . $xml_uncompress . "</HXB2B>");
        return $xml_obj;
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
/*        echo $original_len."-------";
echo $content;*/
        $result_arr = array(
            'content' => $content,
            'length' => $original_len
        );
        return $result_arr;
    }

    private function get_post_data($original_len, $xml_data)
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