<?php


namespace miscellaneous;

require "../extend/miscellaneous/phpqrcode.php";//从运行目录开始
class QeCode
{
    private $path = '';
    public function __construct()
    {
        if (!$this->path){
            $this->path = './uploads/product_qr_code/'.date("Ymd");
        }
    }

    /**
     * 生成二维码图片
     * @param string $url 保存路径
     * @param string $product_sn 文件名称
     */
    public function makeCodeImg($value, $product_sn = '')
    {
        $path = $this->path;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (!$product_sn){
            $product_sn = 'a'.time();
        }
        //$value                  //二维码内容
        $errorCorrectionLevel = 'L';    //容错级别
        $matrixPointSize = 18;           //生成图片大小

        $filename = $path . '/' . $product_sn . '.png';
        \QRcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        return $filename;
/*        $QR = $filename;                //已经生成的原始二维码图片文件
        $QR = imagecreatefromstring(file_get_contents($QR));
        imagejpeg($QR, $product_sn . 'png');*/
    }

    /**
     * 文字生成图片
     * @param $filename 保存路径跟文件名称
     * @param $text  文字
     * @param int $font_size 大小
     * @param string $font 字体路径
     */
    public function makeImgWithStr($text,$filename='',$font_size=18,$font = 'public/static/fonts/MYingHeiPRC-W3.ttf')
    {
        if (!$filename){
            if (!is_dir($this->path)) {
                mkdir($this->path, 0777, true);
            }
            $filename = $this->path.'/b'.time().'.png';
        }
        //图片尺寸
        $im = imagecreatetruecolor(450, 70);
        //背景色
        $white = imagecolorallocate($im, 255, 255, 255);
        //字体颜色
        $black = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 450, 300, $white);
        $txt_max_width = intval(0.8*450);
        $content = "";
        for ($i = 0; $i < mb_strlen($text); $i++) {
            $letter[] = mb_substr($text, $i, 1);
        }
        foreach ($letter as $l) {
            $test_str = $content . " " . $l;
            $test_box = imagettfbbox($font_size, 0, app()->getRootPath().$font, $test_str);
            // 判断拼接后的字符串是否超过预设的宽度。超出宽度添加换行
            if (($test_box[2] > $txt_max_width) && ($content !== "")) {
                $content .= "\n";
            }
            $content .= $l;
        }

        $txt_width = $test_box[2] - $test_box[0];

        $y = 70 * 0.5; // 文字从何处的高度开始
        $x = (450 - $txt_width) / 2; //文字居中
        //  var_dump($x);var_dump($y);
        //   var_dump($content);exit;
        // echo $x;die;
        //文字写入
        imagettftext($im, $font_size, 0, $x, $y, $black, app()->getRootPath().$font, $content); //写 TTF 文字到图中
        //图片保存
        imagejpeg($im, $filename);
        return $filename;
    }

    /**
     * 图片加文字书印
     * @param $path 加载已有图像路径
     * @param string $text 文字
     * @param string $size  大小
     */
    public function addTxetForImg($path, $text = '加棉', $size = '15',$font = "public/static/fonts/MYingHeiPRC-W3.ttf")
    {
        //字体类型
        $font = app()->getRootPath().$font;

        //$img = imagecreatefromjpeg($path);// 加载已有图像
        $typeArr=explode(".",$path);
        switch($typeArr['2'])
        {
            case "png":
                $img=imagecreatefrompng($path);
                break;

            case "jpg":
                $img=imagecreatefromjpeg($path);
                break;
            case "jpeg":
                $img=imagecreatefromjpeg($path);
                break;

            case "gif":
                $img=imagecreatefromgif($path);
                break;
        }

        //给图片分配颜色
        // imagecolorallocate($img, 0xff, 0xcc, 0xcc);
        //设置字体颜色
        $black = imagecolorallocate($img, 255, 0, 0);
        //将ttf文字写到图片中
        imagettftext($img, $size, 0, 30, 285, $black, $font, html_entity_decode($text));
        // ImagePNG($img, "upload/documents/new".time().".jpg");
        imagejpeg($img, "uploads/product_qr_code/c" . time() . ".jpg");
    }

    /**
     * 合并图片,融合合并
     * @param $file_1 原图片路径1
     * @param $file_2 原图片路径2
     * @param $re_file 合成图片保存路径
     */
    public function merageImg($file_1, $file_2, $re_file='')
    {
        if (!$re_file){
            if (!is_dir($this->path)) {
                mkdir($this->path, 0777, true);
            }
            $re_file = $this->path.'/d'.time().'.png';
        }
        //将两张图片分别取到两个画布中
        $image_1 = imagecreatefrompng($file_1);
        $image_2 = imagecreatefromjpeg($file_2);
        //创建一个和大图一样大小的真彩色画布（ps：只有这样才能保证后面copy装备图片的时候不会失真）
        $image_3 = imageCreatetruecolor(imagesx($image_1), imagesy($image_1));
        //为真彩色画布创建白色背景，再设置为透明
        $color = imagecolorallocate($image_3, 255, 255, 255);
        imagefill($image_3, 0, 0, $color);
        imageColorTransparent($image_3, $color);
        //首先将大图画布采样copy到真彩色画布中，不会失真
        imagecopyresampled($image_3, $image_1, 0, 0, 0, 0, imagesx($image_1), imagesy($image_1), imagesx($image_1), imagesy($image_1));
        //再将小图图片copy到已经具有人物图像的真彩色画布中，同样也不会失真
        imagecopymerge($image_3, $image_2, 150, 150, 0, 0, imagesx($image_2), imagesy($image_2), 100);
        //将画布保存到指定的gif文件
        imagejpeg($image_3, $re_file);
        return $re_file;
    }

    //获取拼接图片高度
    public function allImgHeight($arr, $width)
    {
        $height = 0;

        if (count($arr) == count($arr, 1)) {  //一位数组的计算
            foreach ($arr as $key => $value) {
                $info = getimagesize($value);
                $height += $width / $info[0] * $info[1];
            }
        } else {
            foreach ($arr as $key => $value) {  //二维数组的计算

                foreach ($value as $k => $v) {
                    $info = getimagesize($v);
                    $height += $width / $info[0] * $info[1];
                }
            }
        }
        return $height;
    }

    //图片等宽
    public function ImgCompress($src, $out_with = 150)
    {
        // 获取图片基本信息
        list($width, $height, $type, $attr) = getimagesize($src);
        // 获取图片后缀名
        $pic_type = image_type_to_extension($type, false);
        // 拼接方法
        $imagecreatefrom = "imagecreatefrom" . $pic_type;
        // 打开传入的图片
        $in_pic = $imagecreatefrom($src);
        // 压缩后的图片长宽
        $new_width = $out_with;
        $new_height = $out_with / $width * $height;
        // 生成中间图片
        $temp = imagecreatetruecolor($new_width, $new_height);
        // 图片按比例合并在一起。
        imagecopyresampled($temp, $in_pic, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        // 销毁输入图片
        imagejpeg($temp, 'upload/merge' . time() . ".jpg");
        imagedestroy($in_pic);
        return array($temp, $new_width, $new_height);

    }

    /**
     * 合并图片,拼接合并
     * @param array $image_path 需要合成的图片数组
     * @param $save_path 合成后图片保存路径
     * @param string $axis 合成方向
     * @param string $save_type 合成后图片保存类型
     * @return bool|array
     */
    public function CompositeImage(array $image_path, $save_path='', $axis = 'y', $save_type = 'png')
    {
        if (!$save_path){
            if (!is_dir($this->path)) {
                mkdir($this->path, 0777, true);
            }
            $save_path = $this->path.'/'.time().'.png';
        }
        if (count($image_path) < 2) {
            return false;
        }
        //定义一个图片对象数组
        $image_obj = [];
        //获取图片信息
        $width = 0;
        $height = 0;
        foreach ($image_path as $k => $v) {
            $pic_info = getimagesize($v);
            list($mime, $type) = explode('/', $pic_info['mime']);
            //获取宽高度
            $width += $pic_info[0];
            $height += $pic_info[1];
            if ($type == 'jpeg') {
                $image_obj[] = imagecreatefromjpeg($v);
            } elseif ($type == 'png') {
                $image_obj[] = imagecreatefrompng($v);
            } else {
                $image_obj[] = imagecreatefromgif($v);
            }
        }
        //按轴生成画布方向
        if ($axis == 'x') {
            //TODO X轴无缝合成时请保证所有图片高度相同
            $img = imageCreatetruecolor($width, imagesy($image_obj[0]));
        } else {
            //TODO Y轴无缝合成时请保证所有图片宽度相同
            $img = imageCreatetruecolor(imagesx($image_obj[0]), $height);
        }
        //创建画布颜色
        $color = imagecolorallocate($img, 255, 255, 255);
        imagefill($image_obj[0], 0, 0, $color);
        //创建画布
        imageColorTransparent($img, $color);
        imagecopyresampled($img, $image_obj[0], 0, 0, 0, 0, imagesx($image_obj[0]), imagesy($image_obj[0]), imagesx($image_obj[0]), imagesy($image_obj[0]));
        $yx = imagesx($image_obj[0]);
        $x = 0;
        $yy = imagesy($image_obj[0]);
        $y = 0;
        //循环生成图片
        for ($i = 1; $i <= count($image_obj) - 1; $i++) {
            if ($axis == 'x') {
                $x = $x + $yx;
                imagecopymerge($img, $image_obj[$i], $x, 0, 0, 0, imagesx($image_obj[$i]), imagesy($image_obj[$i]), 100);
            } else {
                $y = $y + $yy;
                imagecopymerge($img, $image_obj[$i], 0, $y, 0, 0, imagesx($image_obj[$i]), imagesy($image_obj[$i]), 100);
            }
        }
        //设置合成后图片保存类型
        if ($save_type == 'png') {
            imagepng($img, $save_path);
        } elseif ($save_type == 'jpg' || $save_type == 'jpeg') {
            imagejpeg($img, $save_path);
        } else {
            imagegif($img, $save_path);
        }
        return $save_path;

    }

    /**
     * 生成带编号说明的二维码 (生成二维码  文字生成图片 图片合并拼接)
     * @param $content 二维码内容
     * @param $text 底部文字
     * @param $sn_product 合成路径
     */
    public function makeMergerImg($content,$text,$sn_product){
        $a1 = $this->makeCodeImg($content);
        $a2 = $this->makeImgWithStr($text);
        $a3 = $this->CompositeImage([$a1,$a2],$sn_product);
        unlink($a1);
        unlink($a2);
        return $a3;
    }

    /**
     * 生成压缩文件
     * @param array  $file_list 需要压缩的文件 ['upload/product_qr_code/cb01-000001-.jpg','upload/product_qr_code/cb01-000002-.jpg']
     * @param string $file_name 生成压缩zip文件 $file_name 最终生成的文件名,包含路径
     * @return string
     */
    public function makeZip(array $file_list, $file_name='')
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
        if (!$file_name){
            $file_name = $this->path.'/z'.time().'.zip';
        }
        if (file_exists($file_name)) {
            unlink($file_name);
        }
        //重新生成文件
        $zip = new \ZipArchive();
        if ($zip->open($file_name, \ZIPARCHIVE::CREATE) !== TRUE) {
            exit('无法打开文件，或者文件创建失败');
        }
        foreach ($file_list as $val) {
            if (file_exists($val)) {
                $zip->addFile($val);
            }
        }
        $zip->close();//关闭
        if (!file_exists($file_name)) {
            exit('无法找到文件'); //即使创建，仍有可能失败
        }
        return $file_name;
    }

    /**
     * 下载
     * @param $file 需要下载的文件路径
     */
    public function download($file){
        if ( file_exists ( $file )) {
            header ( 'Content-Description: File Transfer' );
            header ( 'Content-Type: application/octet-stream' );
            header ( 'Content-Disposition: attachment; filename=' . basename ( $file ));
            header ( 'Content-Transfer-Encoding: binary' );
            header ( 'Expires: 0' );
            header ( 'Cache-Control: must-revalidate' );
            header ( 'Pragma: public' );
            header ( 'Content-Length: ' . filesize ( $file ));
            ob_clean ();
            flush ();
            readfile ( $file );
            exit;
        }
    }
}