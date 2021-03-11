private function check_gifcartoon($image_file){

        $fp = fopen($image_file,'rb');

        $image_head = fread($fp,1024);

        fclose($fp);

        return
            preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)?false:true;


    }

    private function compressedImage($imgsrc, $imgdst) {
        list($width, $height, $type) = getimagesize($imgsrc);

        $new_width = $width;//压缩后的图片宽
        $new_height = $height;//压缩后的图片高

        if($width >= 900){
            $per = 900 / $width;//计算比例
            $new_width = $width * $per;
            $new_height = $height * $per;
        }

        switch ($type) {
            case 1:
                $giftype = $this->check_gifcartoon($imgsrc);
                if ($giftype) {

                    $image_wp = imagecreatetruecolor($new_width, $new_height);
                    $image = imagecreatefromgif($imgsrc);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    //90代表的是质量、压缩图片容量大小
                    imagejpeg($image_wp, $imgdst, 90);
                    imagedestroy($image_wp);
                    imagedestroy($image);
                }
                break;
            case 2:

                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromjpeg($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //90代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 90);
                imagedestroy($image_wp);
                imagedestroy($image);
                break;
            case 3:

                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefrompng($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //90代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 90);
                imagedestroy($image_wp);
                imagedestroy($image);
                break;
        }
    }
