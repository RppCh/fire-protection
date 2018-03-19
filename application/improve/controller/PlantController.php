<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 13:51
 */

namespace app\improve\controller;

use app\improve\controller\Helper;
use app\improve\model\PlantDb;
use app\improve\model\CommonDb;
use app\improve\validate\Plant;
use think\Controller;
use think\Error;
use think\Exception;
use think\File;
use think\Validate;

class PlantController extends Controller
{

    function ls($smaple = false)
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new Validate([
            'per_page' =>'require|number|max:500|min:1',
            'current_page' =>'require|number|min:1',
            'name' =>'max:16',
            'is_localed' =>'in:-1,1',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $dbRes = PlantDb::ls($data, $smaple);
        return Helper::reJson($dbRes);
    }

    function sampleLs()
    {
      return $this->ls(true);
    }


    function local()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Plant.local');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PlantDb::local($data['ids']);
        return Helper::reJson($dbRes);
    }

    function query()
    {
         $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Plant.query');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PlantDb::query($data['id']);
        return Helper::reJson($dbRes);
    }

    function edit()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Plant.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PlantDb::edit($data);
        return Helper::reJson($dbRes);
    }

    function saveAttach()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'Plant.query');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //检测是否有这个id
        $plant = PlantDb::queryAttachPath($data['id']);
        if (!$plant[0]) Helper::reJson($plant);
        //附件上传
        $attach = request()->file('attach');
        if (empty($attach)) return Helper::reJson(Errors::NO_FILE);
        $data['attach_size'] = $attach->getSize();
        if (!$attach->checkSize(100 * 1024 * 1024)) return reJson(Errors::MAX_FILE_SIZE);
        $preName = DS . 'plant' . DS . 'attach_' . $data['id'] . DS . $attach->getInfo()['name'];
        $uploadRes = $this->upload($attach, $preName);
        if (!is_array($uploadRes)) return Helper::reJson($uploadRes);
        $data['attach'] = $uploadRes[0];
        $dbRes = PlantDb::edit2($data);
        if (!$dbRes[1]) return Helper::reJson($dbRes);
        $path = $plant[1][$data['id']];
        if (!empty($path)) Helper::deleteFile($path);
        return Helper::reJson([true , $data]);
    }












    /*
     * 植物信息维护保存图片
     */
    function saveImage()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'Plant.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //检测是否有这个id
        $plant = PlantDb::query($data['id']);
        if (!$plant[0]) Helper::reJson($plant);
        //看数据中已经有多少张图片了，最多允许6张,最大2M
        $imageCount = PlantDb::queryImageCount($data['id']);
        if (is_string($imageCount)) return Helper::reJson($imageCount);
        if ($imageCount > 5) return Helper::reJson(Errors::IMAGE_COUNT_ERROR);
        $image = request()->file('image');
        if (empty($image)) return Helper::reJson(Errors::IMAGE_NOT_FIND);
        if (!$image->checkImg()) return Helper::reJson(Errors::FILE_TYPE_ERROR);
        if (!$image->checkSize(2 * 1024 * 1024)) return Helper::reJson(Errors::IMAGE_FILE_SIZE_ERROR);
        //上传
        $preName = DS . 'plant' . DS . 'image_' . $data['id'] . DS .$image->getInfo()['name'];
        $uploadRes = UploadHelper::upload($image, $preName);
        if (!$uploadRes[0]) return Helper::reJson($uploadRes);
        //更新数据库
        $dbRes = PlantDb::saveImage($data['id'], $uploadRes[1]);
        return Helper::reJson($dbRes);
    }

    /*
     * 植物信息维护删除图片
     */
    function deleteImage()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Plant.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //检测是否有这个id
        $plant = PlantDb::query($data['id']);
        if (!$plant[0]) Helper::reJson($plant);
        $dbRes = PlantDb::deleteImage($data['id'], $data['imageId']);
        return Helper::reJson($dbRes);
    }

    private function upload($file, $preName)
    {
        $info = $file->move(Errors::FILE_ROOT_PATH, $preName);
        if (!$info) return Errors::SAVE_FILE_ERROR;
        $imageUrl = $info->getRealPath();
        $imageUrl = strstr($imageUrl, "file");
        $imageUrl = substr($imageUrl, 5);
        return [$imageUrl];
    }
}

?>