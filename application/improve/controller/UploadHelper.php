<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/9
 * Time: 16:39
 */
namespace app\improve\controller;
class UploadHelper{

    static function upload($file, $preName)
    {
        //重命名
        $i = 1;
        $p = strrpos($preName,'.');
        $q = substr($preName, 0 , $p);
        $h = substr($preName, $p);
        while (is_file(Errors::FILE_ROOT_PATH.$preName)) {
            $preName = $q.'('.$i.')'.$h;
            $i++;
        }
//        $preName=iconv('UTF-8', 'GB2312', $preName);
        $info = $file->move(Errors::FILE_ROOT_PATH,$preName);
        if (!$info) return Errors::FILE_SAVE_ERROR;
//        $imageUrl=iconv('GB2312', 'UTF-8', $info->getRealPath());
        $imageUrl=$info->getRealPath();
        $imageUrl = strstr($imageUrl, "file");
        $imageUrl = substr($imageUrl, 5);
        return [true , $imageUrl];
    }

    static function uplodImage($image, $folder)
    {
        $a = Helper::checkImage(0, $image);
        if (!$a[0]) return $a;
        //DS . 'task' . DS . 'image_' . $id
        $preName =  $folder . DS . $image->getInfo()['name'];
        return [true, self::upload($image, $preName)];
    }

}
?>