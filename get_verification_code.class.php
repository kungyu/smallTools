<?php
/**
 * Created by PhpStorm.
 * User: kung
 * Date: 18-9-19
 * Time: 下午3:33
 */







class get_img_content{

    /*
  *取得图片路径和图片尺寸
  */

    public function __construct($Image)
    {
        $this->ImagePath = $Image;
        $this->ImageSize = getimagesize($Image);
    }


 /*
  *获取图像标识符，保存到ImageInfo，只能处理bmp,png,jpg图片
  *ImageCreateFromBmp是我自己定义的函数，最后会给出
  */

 function getInfo(){
         $filetype = substr($this->ImagePath,-3);
     if($filetype == 'bmp'){
               $this->ImageInfo = $this->imagecreatefrombmp($this->ImagePath);
     }elseif($filetype == 'jpg' || $filetype == 'jpeg'){
                $this->ImageInfo = imagecreatefromjpeg($this->ImagePath);
     }elseif($filetype == 'png'){
                 $this->ImageInfo = imagecreatefrompng($this->ImagePath);
    }
 }

    /*获取图片RGB信息*/
 function getRgb(){
         $rgbArray = array();
     $res = $this->ImageInfo;
     $size = $this->ImageSize;
    $wid = $size['0'];
     $hid = $size['1'];
    for($i=0; $i < $hid; ++$i){
                for($j=0; $j < $wid; ++$j){
                        $rgb = imagecolorat($res,$j,$i);
           $rgbArray[$i][$j] = imagecolorsforindex($res, $rgb);
        }
     }
    return $rgbArray;
 }


    /*
      *获取灰度信息
      */
    function getGray(){
        $grayArray = array();
        $size = $this->ImageSize;
        $rgbarray = $this->getRgb();
        $wid = $size['0'];
        $hid = $size['1'];
        for($i=0; $i < $hid; ++$i){
            for($j=0; $j < $wid; ++$j){
                $grayArray[$i][$j] = (299*$rgbarray[$i][$j]['red']+587*$rgbarray[$i][$j]['green']+144*$rgbarray[$i][$j]['blue'])/1000;
            }
        }
        return $grayArray;
    }

    /*根据灰度信息打印图片*/
 function printByGray(){
     $size = $this->ImageSize;
      $grayArray = $this->getGray();
      $wid = $size['0'];
     $hid = $size['1'];
     for($k=0;$k<25;$k++){
                 echo $k."\n";
          for($i=0; $i < $hid; ++$i){
                         for($j=0; $j < $wid; ++$j){
                                 if($grayArray[$i][$j] < $k*10){
                                        echo '■';
                }else{
                                       echo '□';
                 }
             }
            echo "|\n";
        }
        echo "---------------------------------------------------------------------------------------------------------------\n";
    }
 }

    /*
      *根据自定义的规则，获取二值化二维数组
      *@return  图片高*宽的二值数组（0,1）
     */
 function getErzhi(){
        $erzhiArray = array();
        $size = $this->ImageSize;
     $grayArray = $this->getGray();
     $wid = $size['0'];
    $hid = $size['1'];
    for($i=0; $i < $hid; ++$i){
                 for($j=0; $j <$wid; ++$j){
                         if( $grayArray[$i][$j]    < 70 ){
                                 $erzhiArray[$i][$j]=1;
            }else{
                                $erzhiArray[$i][$j]=0;
           }
        }
     }
//     $erzhiArray = array_slice($erzhiArray,3,13);
     return $erzhiArray;
 }

 function get_number_rec(){

 }


    function imagecreatefrombmp( $filename ){
        if ( !$f1 = fopen( $filename, "rb" ) )
            return FALSE;

        $FILE = unpack( "vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread( $f1, 14 ) );
        if ( $FILE['file_type'] != 19778 )
            return FALSE;

        $BMP = unpack( 'Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' . '/Vcompression/Vsize_bitmap/Vhoriz_resolution' . '/Vvert_resolution/Vcolors_used/Vcolors_important', fread( $f1, 40 ) );
        $BMP['colors'] = pow( 2, $BMP['bits_per_pixel'] );
        if ( $BMP['size_bitmap'] == 0 )
            $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
        $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
        $BMP['bytes_per_pixel2'] = ceil( $BMP['bytes_per_pixel'] );
        $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] -= floor( $BMP['width'] * $BMP['bytes_per_pixel'] / 4 );
        $BMP['decal'] = 4 - (4 * $BMP['decal']);
        if ( $BMP['decal'] == 4 )
            $BMP['decal'] = 0;

        $PALETTE = array();
        if ( $BMP['colors'] < 16777216 ){
            $PALETTE = unpack( 'V' . $BMP['colors'], fread( $f1, $BMP['colors'] * 4 ) );
        }

        $IMG = fread( $f1, $BMP['size_bitmap'] );
        $VIDE = chr( 0 );

        $res = imagecreatetruecolor( $BMP['width'], $BMP['height'] );
        $P = 0;
        $Y = $BMP['height'] - 1;
        while( $Y >= 0 ){
            $X = 0;
            while( $X < $BMP['width'] ){
                if ( $BMP['bits_per_pixel'] == 32 ){
                    $COLOR = unpack( "V", substr( $IMG, $P, 3 ) );
                    $B = ord(substr($IMG, $P,1));
                    $G = ord(substr($IMG, $P+1,1));
                    $R = ord(substr($IMG, $P+2,1));
                    $color = imagecolorexact( $res, $R, $G, $B );
                    if ( $color == -1 )
                        $color = imagecolorallocate( $res, $R, $G, $B );
                    $COLOR[0] = $R*256*256+$G*256+$B;
                    $COLOR[1] = $color;
                }elseif ( $BMP['bits_per_pixel'] == 24 )
                    $COLOR = unpack( "V", substr( $IMG, $P, 3 ) . $VIDE );
                elseif ( $BMP['bits_per_pixel'] == 16 ){
                    $COLOR = unpack( "n", substr( $IMG, $P, 2 ) );
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }elseif ( $BMP['bits_per_pixel'] == 8 ){
                    $COLOR = unpack( "n", $VIDE . substr( $IMG, $P, 1 ) );
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }elseif ( $BMP['bits_per_pixel'] == 4 ){
                    $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
                    if ( ($P * 2) % 2 == 0 )
                        $COLOR[1] = ($COLOR[1] >> 4);
                    else
                        $COLOR[1] = ($COLOR[1] & 0x0F);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }elseif ( $BMP['bits_per_pixel'] == 1 ){
                    $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
                    if ( ($P * 8) % 8 == 0 )
                        $COLOR[1] = $COLOR[1] >> 7;
                    elseif ( ($P * 8) % 8 == 1 )
                        $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                    elseif ( ($P * 8) % 8 == 2 )
                        $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                    elseif ( ($P * 8) % 8 == 3 )
                        $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                    elseif ( ($P * 8) % 8 == 4 )
                        $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                    elseif ( ($P * 8) % 8 == 5 )
                        $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                    elseif ( ($P * 8) % 8 == 6 )
                        $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                    elseif ( ($P * 8) % 8 == 7 )
                        $COLOR[1] = ($COLOR[1] & 0x1);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }else
                    return FALSE;
                imagesetpixel( $res, $X, $Y, $COLOR[1] );
                $X++;
                $P += $BMP['bytes_per_pixel'];
            }
            $Y--;
            $P += $BMP['decal'];
        }
        fclose( $f1 );

        return $res;
    }



}
