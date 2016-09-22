<?php

public function imgup(){
    $result = array('status' => 0);
    $extend_limit = array('.gif','.png','.jpg','.jpeg');
    $type_limit = array("image/gif","image/jpeg","image/pjpeg","image/png");
    if(isset($_FILES['image'])){
        if($_FILES['image']['error'] == 0){
            $dot_pos = strripos($_FILES['image']['name'],'.');
            $file_extend = substr($_FILES['image']['name'],$dot_pos);
            if(in_array($file_extend,$extend_limit) && in_array($_FILES['image']['type'],$type_limit) &&  ($_FILES["image"]["size"] < 100000)){
                $real_file_name = $this->getRandChar(5).time().$file_extend;
                $sys_path = dirname(__FILE__); // 服务器端的绝对路径
                $real_file_path = $sys_path.$real_file_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $real_file_path)) {
                        $url_path = 'url_path'; //上传文件在url访问时候的路径
                        $file_url_path = $url_path . $real_file_name;
                        $result['status'] = 1;
                        $result['imgurl'] = $file_url_path;
                    } else {
                        $result['status'] = 4;
                        $result['desc'] = '图片上传失败';
                    }
            }else{
                $result['status'] = 3;
                $result['desc']   = '请选择图片文件上传';
            }
        }else{
            $result['status'] = 2;
            $result['desc']   = '文件上传失败';
        }
    }
    echo json_encode($result);
}

private function getRandChar($length){
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];
    }
    return $str;
}
?>
