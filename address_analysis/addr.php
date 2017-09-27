<?php
	public function addrs(){
        $this->load->helper('address.two.php');
        $this->load->helper('address.city.php');
        $province = addrs();
        $citys = city();
        $addr=$this->input->post('addr');
        $addr_local = $addr;
        $what_arr = array("。","&#x3002;","，","&#65292;","…","&#8230;",',','.',' ',"\xc2\xa0","\n","\r","\t",'"',"'",'“',"‘",'!','！',':','：',';','`','`','~','～','(',')','（','）','|','|','\\','/','?','>','<','《','》','*','×','#','#','@','@','%','%',
            '名字','姓名','收件人','收货人','收货方','发件人','发货人','发件方','发货方','手机','电话','-','；');
        $what_arr_org = array_pad(array(),count($what_arr),'');
        $addr_orig = str_replace($what_arr,$what_arr_org,$addr);
        $addr = str_replace('-','',$addr);
        $what_arr_sp = array_pad(array(),count($what_arr),'|');
        $addr =str_replace($what_arr,$what_arr_sp,$addr);

        $district_rep = $this->get_district($addr,$province,$citys);
        $sheng = $district_rep['sheng'];
        $shi = $district_rep['shi'];
        $qu = $district_rep['qu'];
	
        $result['sheng'] = $sheng;
        $result['shi'] = $shi;
        $result['qu'] = $qu;
        $result['tel'] = '';
        $addr = str_replace($qu,'|',$addr);
//        preg_match('/[^0-9+]*(?P<tel>(\+86[0-9]{11})|([0-9]{11})|([0-9]{3,4}-[0-9]{7,10}))[^0-9+]*/',$addr,$tels);
        preg_match("/1[34578]\d{9}/", $addr, $tels);
        $tel = '';

        if(!empty($tels)) {
            $tel = $tels[0];

        }else{
            //0917-6444257
            preg_match("/0\d{2,3}\-\d{7,8}/", $addr_local, $tels);
            if(!empty($tels)){
                $tel = $tels[0];
            }
        }
        $result['tel'] = $tel;


        $arr_place = array('楼','座','栋','橦','单元','号','室','收','电话','码');
        $arr_place_rep = array_pad(array(),count($arr_place),'|');
        $addr_copy = str_replace($arr_place,$arr_place_rep,$addr);
        $addr_copy = preg_replace('/[0-9]+/','|',$addr_copy);
        $arr_add_copy = explode('|',$addr_copy);

        $name = '';
        $arr_name = array();

        foreach($arr_add_copy as $add_copy_val){

            if(mb_strlen(trim($add_copy_val)) > 0 && mb_strlen(trim($add_copy_val)) < 5 && !preg_match('/[0-9]/', $add_copy_val) && !preg_match('/[A-Za-z]/', $add_copy_val)){
                $arr_name[] = $add_copy_val;
            }
        }
        $sub_len = 99;
        foreach($arr_name as $name_val){
            $name_pos = mb_strpos($addr_orig,$name_val);
            $name_len = mb_strlen($name_val);
            $tel_pos  = mb_strpos($addr_orig,$tel);
            if(($name_pos + $name_len) == $tel_pos || $tel_pos + 11 == $name_pos){
                $name = $name_val;
                break;
            }
            if($name_pos < $tel_pos){
                if($tel_pos - $name_pos  < $name_len + 3){
                    $sub_len_son = $tel_pos - $name_pos;
                    if($sub_len_son < $sub_len) {
                        $sub_len = $sub_len_son;
                        $name = $name_val;
                    }
                }
            }
            if($tel_pos < $name_pos){
                if($name_pos - $tel_pos < 14){
                    $sub_len_son = $name_pos - $tel_pos;
                    if($sub_len_son < $sub_len){
                        $sub_len = $sub_len_son;
                        $name = $name_val;
                    }
                }
            }

        }

        if(empty($name) && !empty($arr_name))
            $name = $arr_name[0];
        $arr_unit = array('米','箱','件','个','单','套','盒','包','瓶','公斤','斤','千克','kg','Kg','KG','克','g','G','只','头','份');
        if(in_array(trim(trim($name),',:.，：。'),$arr_unit))
            $name = '';

        if(empty($name)){
            $arr_special = array(',','.',' ','，','。',':','：');
            $arr_special_rep = array_pad(array(),count($arr_special),'|');
            $add_get_name = explode('|',str_replace($arr_special,$arr_special_rep,$addr_local));
            $arr_name = array();
            foreach($add_get_name as $get_name){
                $get_name = preg_replace('/[0-9]/','',$get_name);
                if(mb_strlen(trim($get_name)) > 1 && mb_strlen(trim($get_name)) < 5 && !preg_match('/[0-9]/', $get_name)){
                    $arr_name[] = $get_name;
                }
            }

            if(!empty($arr_name))
                foreach($arr_name as $name_val){
                    if(mb_strpos($name_val,'收')) {
                        $name = str_replace('收', '', $name_val);
                        break;
                    }
                    if((mb_strpos($addr_orig,$name_val) + mb_strlen($name_val)) == mb_strpos($addr_orig,$tel) || (mb_strpos($addr_orig,$name_val) + mb_strlen($name_val)) + 1 == mb_strpos($addr_orig,$tel)){
                        $name = $name_val;
                        break;
                    }

                }
        }

        if(empty($name)){
            $name_addr_no_method = str_replace('|','',$addr);
            $name = mb_substr($name_addr_no_method,-2);
        }
        $result['name'] = $name;
      $user_info = array($sheng,$shi,$qu,$tel,$name);
      $user_info_rep = array('','','','','');
      $addr = str_replace($user_info,$user_info_rep,$addr_local);
        $arr_special = array(',,','..',' ','，，','。',':','：','，','-',';','；','.');
        $arr_special_rep = array_pad(array(),count($arr_special),'');
        $addr = str_replace($arr_special,$arr_special_rep,$addr);
        $none_str = array('收件人','手机','号码','电话','名字','姓名','发件人','发货人','收货人','地址');
        $none_str_rep = array_pad(array(),count($none_str),'');;
        $addr = str_replace($none_str,$none_str_rep,$addr);
        $addr = str_replace($tel,'',$addr);
	$sheng_sub = mb_substr($sheng,0,mb_strlen($sheng)-1);
	$shi_sub = mb_substr($shi,0,mb_strlen($shi)-1);
	$qu_sub = mb_substr($qu,0,mb_strlen($qu)-1);
	$repg1="/$sheng_sub/";
	$repg2="/$shi_sub/";
	$repg3="/$qu_sub/";
	$addr = preg_replace($repg1,'',$addr,1);
	$addr = preg_replace($repg2,'',$addr,1);
	$addr = preg_replace($repg3,'',$addr,1);
	$addr = preg_replace('/省/','',$addr,1);
	$addr = preg_replace('/市/','',$addr,1);
	$qu_repg = $qu_sub = mb_substr($qu,-1);
	$qu_rep = "/$qu_repg/";
	$addr = preg_replace($qu_rep,'',$addr,1);
        $result['addmore'] = $addr;
      echo json_encode($result);
      exit;
    }


private function get_district($addr,$province,$citys){
        $shi = $sheng = $qu = '';
        $citys_keys = array_keys($citys);
        //首先匹配带市的三个字
        preg_match_all('/(.{6})市/', $addr, $arr);
        if(!empty($arr) && !empty($arr[0])){
            foreach($arr as $arr_val){
                if(mb_strpos($arr_val[0],'市') > 0 ){
                    if($arr_val[0] == '北京市' || $arr_val[0] == '上海市' || $arr_val[0] == '重庆市' || $arr_val[0] == '天津市') {
                        $sheng = $arr_val[0] ;
                        $shi = $arr_val[0];
                        foreach($province[$sheng] as $pro_val){
                            if(mb_strpos($addr,$pro_val) !== false){
                                $qu = $pro_val;
                            }
                        }
                        break;
                    }
                    if(in_array($arr_val[0],$citys_keys)){
                        $shi = mb_substr($arr_val[0],0,mb_strlen($arr_val[0])-1);
                        break;
                    }
                }
            }
        }
        //匹配带市的四个字
        if(empty($shi)){
            preg_match_all('/(.{9})市/', $addr, $arr);
            if(!empty($arr) && !empty($arr[0])){
                foreach($arr as $arr_val){
                    if(mb_strpos($arr_val[0],'市') > 0 ){
                        if(in_array($arr_val[0],$citys_keys)){
                            $shi = mb_substr($arr_val[0],0,mb_strlen($arr_val[0])-1);
                            break;
                        }
                    }
                }
            }
        }


        //当省为空的时候
        if(empty($sheng))
            foreach($province as $key=>$val){
                if(empty($sheng) && !empty($shi)) {
                    if (in_array($shi, $val)) {
                        $sheng = $key;
                        $shi = $shi.'市';
                        break;
                    }
                }
                //遍历省下所有地级市
                if(empty($sheng) && empty($shi)){
                    foreach ($val as $city_item) {
                        if (mb_strlen($city_item) == 3)
                            $city_item_sub = mb_substr($city_item, 0, 2);
                        elseif(mb_strlen($city_item) > 3)
                            $city_item_sub = mb_substr($city_item, 0, 3);
                        else
                            $city_item_sub = $city_item;
                        if (mb_strpos($addr, $city_item_sub) !== false) {
                            $shi = $city_item;
                            $sheng = $key;
                            $shi = isset($citys[$shi]) ? $shi : $shi . '市';
                            foreach ($citys[$shi] as $city_val) {
                                $city_val_sub = mb_substr($city_val, 0, 2);
                                if (mb_strpos($addr, $city_val_sub) !== false) {
                                    $qu = $city_val;
                                    if ($city_val_sub == '伊宁')
                                        if (mb_strpos($addr, $city_val) !== false) {
                                            $qu = $city_val;
                                        }
                                    if ($city_val_sub == '临夏')
                                        if (mb_strpos($addr, $city_val) !== false) {
                                            $qu = $city_val;
                                        }
                                    break;
                                }
                            }

                            break;
                        }
                    }
                    if ($shi)
                        break;
                }
            }

            //省份依旧为空 遍历所有省
        if(empty($sheng)){
            foreach($province as $province_key => $province_val){
                $province_sub = mb_substr($province_key,0,2);
                if(mb_strpos($addr,$province_sub) !== false){
                    $sheng = $province_key;
                    break;
                }
            }
        }

        //遍历所有城市查找地级市
        if(empty($shi) && (empty($sheng) || empty($qu))){
            if(!empty($sheng)){
                $conte = 0;
                foreach($province[$sheng] as $province_val){
                    $city_name = isset($citys[$province_val])?$province_val:$province_val.'市';
                    foreach($citys[$city_name] as $city_item){
                        $last = mb_substr($city_item, -1);
                        if (($last == '市' || $last == '县') && mb_strlen($city_item) > 2)
                            $city_val_sub = mb_substr($city_item, 0, mb_strlen($city_item) - 1);
                        else
                            $city_val_sub = mb_substr($city_item, 0, 3);
                        if (mb_strpos($addr, $city_val_sub) !== false) {

                            if(mb_substr($city_item,-1) == '区' && empty($shi)) {
                                $conte = 1;
                                $shi = $city_name;
                                $qu = $city_item;
                            }else{
                                $shi = $city_name;
                                $qu = $city_item;
                                $conte = 0;
                                break;
                            }

                        }
                    }
                    if($shi && !$conte)
                        break;
                }
            }
            else {
                foreach ($citys as $city_key => $city_val_item) {
                    foreach ($city_val_item as $city_val) {
                        $last = mb_substr($city_val, -1);
                        if (($last == '市' || $last == '县') && mb_strlen($city_val) > 2)
                            $city_val_sub = mb_substr($city_val, 0, mb_strlen($city_val) - 1);
                        else
                            $city_val_sub = mb_substr($city_val, 0, 3);
                        if (mb_strpos($addr, $city_val_sub) !== false) {
                            $qu = $city_val;
                            $shi = $city_key;
                            foreach ($province as $province_key => $province_val) {
                                if (in_array($shi, $province_val) || in_array(mb_substr($shi, 0, mb_strlen($shi) - 1), $province_val)) {
                                    $sheng = $province_key;
                                    break;
                                }
                            }
                        }
                        if ($qu)
                            break;
                    }
                    if ($qu)
                        break;
                }
            }
        }

        //根据市遍历查找区
        if(empty($qu) && !empty($shi)){

            foreach ($citys[$shi] as $city_val) {
                $last = mb_substr($city_val, -1);
                if (($last == '市' || $last == '县') && mb_strlen($city_val) > 2)
                    $city_val_sub = mb_substr($city_val, 0, mb_strlen($city_val) - 1);
                else
                    $city_val_sub = mb_substr($city_val, 0, 2);
                if (mb_strpos($addr, $city_val_sub) !== false) {
                    $qu = $city_val;
                }
                if ($qu)
                    break;
            }
        }
        return compact('sheng','shi','qu');
    }





?>
