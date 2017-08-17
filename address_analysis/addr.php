<?php
	$this->load->helper('address.two.php');
        $this->load->helper('address.city.php');
        $province = addrs();
        $citys = city();
        $province_keys = array_keys($province);
        $addr=$this->input->post('addr');
        $addr_local = $addr;
        $what_arr = array("。","&#x3002;","，","&#65292;","…","&#8230;",',','.',' ',"\xc2","\n","\r");
        $what_arr_sp = array_pad(array(),count($what_arr),'');
        $addr =str_replace($what_arr,$what_arr_sp,$addr);
        $addr_orig = $addr;
        $sheng_pos = mb_strpos($addr,'省');
        $sheng = '';
        if($sheng_pos){
            $sheng = mb_substr($addr,$sheng_pos-2,3);
            if(!in_array(mb_substr($sheng,0,mb_strlen($sheng)-1),$province_keys))
                $sheng = mb_substr($addr,$sheng_pos-3,4);
            if(!in_array(mb_substr($sheng,0,mb_strlen($sheng)-1),$province_keys))
                $sheng = '';
            $addr = str_replace($sheng,'|',$addr);
        }else{
            if(strpos($addr,'北京') !== false)
                $sheng = '北京市';
            if(strpos($addr,'上海') !== false)
                $sheng = '上海市';
            if(strpos($addr,'天津') !== false)
                $sheng = '天津市';
            if(strpos($addr,'重庆') !== false)
                $sheng = '重庆市';
        }


        preg_match_all('/(.{6})市/', $addr, $arr);
        $shi = '';
        if(!empty($arr)&&!empty($arr[0]))
            $shi = $arr[0][0];
        if(empty($shi) && in_array($sheng,array('上海市','北京市','重庆市','天津市'))){
            $shi = $sheng;
        }
        if(!empty($shi) && empty($sheng)){
            foreach($province as $k=>$v){
                if(in_array(mb_substr($shi,0,mb_strlen($shi)-1),$v)){
                    $sheng = $k.'省';
                    $addr = str_replace($k,'',$addr);
                    break;
                }
            }
        }

        if(empty($shi) && !empty($sheng)){
            $sheng_key = mb_substr($sheng,0,mb_strlen($sheng)-1);
            if(isset($province[$sheng_key])){
                foreach($province[$sheng_key] as $vv){
                    if(mb_strpos($addr,$vv) !== false){
                        $shi = $vv.'市';
                        break;
                    }
                }
            }
        }
        $result['sheng'] = $sheng;
        $result['shi'] = $shi;
        $addr = str_replace($shi,'|',$addr);
        preg_match_all('/(.{6})区/', $addr, $arr);
        $qu = '';
        if(!empty($arr) && !empty($arr[0]))
            $qu = $arr[0][0];
        else{
            preg_match_all('/(.{6})市/', $addr, $arr);
            if(!empty($arr) && !empty($arr[0]))
                $qu = $arr[0][0];
            else{
                preg_match_all('/(.{6})县/', $addr, $arr);
                if(!empty($arr) && !empty($arr[0]))
                    $qu = $arr[0][0];
            }
        }
        if(empty($qu) && !empty($shi)){
            if(isset($citys[$shi])){
                foreach($citys[$shi] as $vv){
                    if(mb_strpos($addr,mb_substr($vv,0,mb_strlen($vv)-1)) !== false){
                        $qu = $vv;
                        break;
                    }
                }
            }
        }
        $result['qu'] = $qu;
        $addr = str_replace($qu,'|',$addr);
        preg_match('/[^0-9+]*(?P<tel>(\+86[0-9]{11})|([0-9]{11})|([0-9]{3,4}-[0-9]{7,10}))[^0-9+]*/',$addr,$tels);
        $tel=$tels[1];
        $result['tel'] = $tel;
        $addr = str_replace($tel,'|',$addr);
        $arr_place = array('楼','座','栋','橦','单元','号','室','收');
        $arr_place_rep = array_pad(array(),8,'|');
        $addr_copy = str_replace($arr_place,$arr_place_rep,$addr);
        $addr_copy = preg_replace('/[0-9]+/','|',$addr_copy);
        $arr_add_copy = explode('|',$addr_copy);

        $name = '';
        $arr_name = array();
        foreach($arr_add_copy as $add_copy_val){
            if(mb_strlen(trim($add_copy_val)) > 0 && mb_strlen(trim($add_copy_val)) < 5 && !preg_match('/[0-9]/', $add_copy_val)){
                $arr_name[] = $add_copy_val;
            }
        }

        foreach($arr_name as $name_val){
            if((mb_strpos($addr_orig,$name_val) + mb_strlen($name_val)) == mb_strpos($addr_orig,$tel) || (mb_strpos($addr_orig,$name_val) + mb_strlen($name_val)) + 1 == mb_strpos($addr_orig,$tel)){
                $name = $name_val;
                break;
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
        if(empty($name))
            $name = '无名';
        $result['name'] = $name;
      $user_info = array($sheng,$shi,$qu,$tel,$name);
      $user_info_rep = array('','','','','');
      $addr = str_replace($user_info,$user_info_rep,$addr_local);
        $arr_special = array(',,','..',' ','，，','。',':','：');
        $arr_special_rep = array_pad(array(),count($arr_special),'');
        $result['addmore'] = str_replace($arr_special,$arr_special_rep,$addr);
      echo json_encode($result);
      exit;

?>
