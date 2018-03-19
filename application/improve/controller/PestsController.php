<?php
/**
 * Created by PhpStorm.
 * User: LiuTao
 * Date: 2017/12/2/002
 * Time: 16:58
 */

namespace app\improve\controller;

use app\improve\model\PestsDb;
use app\improve\validate\Pests;
use think\Controller;
use think\Validate;

class PestsController extends Controller
{

    function ls($sample = false)
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new Validate([
            'per_page' =>'require|number|max:500|min:1',
            'current_page' =>'require|number|min:1',
            'name' =>'max:16',
            'is_localed' =>'in:-1,1',
            'type' =>'in:N,Q,H',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $dbRes = PestsDb::ls($data, $sample);
        return Helper::reJson($dbRes);
    }

    function sampleLs(){
        return $this->ls(true);
    }


    function local()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Pests.local');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PestsDb::local($data['ids']);
        return Helper::reJson($dbRes);
    }

    function query()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Pests.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PestsDb::query($data['id']);
        return Helper::reJson($dbRes);
    }

    function edit()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Pests.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PestsDb::edit($data);
        return Helper::reJson($dbRes);
    }

    function saveAttach()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'Pests.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //检测是否有这个id
        $plant = PestsDb::queryAttachPath($data['id']);
        if (!$plant[0]) Helper::reJson($plant);
        //附件上传
        $attach = request()->file('attach');
        if (empty($attach)) return Helper::reJson(Errors::NO_FILE);
        $data['attach_size'] = $attach->getSize();
        if (!$attach->checkSize(100 * 1024 * 1024)) return Helper::reJson(Errors::MAX_FILE_SIZE);
        $preName = DS . 'pest' . DS . 'attach_' . $data['id'] . DS . $attach->getInfo()['name'];
        $uploadRes = UploadHelper::upload($attach, $preName);
        if (!$uploadRes[0]) return Helper::reJson($uploadRes);
        $data['attach'] = $uploadRes[1];
        $dbRes = PestsDb::edit2($data);
        if (!$dbRes[0]) return Helper::reJson($dbRes);
        $path = $plant[1][$data['id']];
        if (!empty($path)) Helper::deleteFile($path);
        return Helper::reJson([true , $data]);
    }

    /*
     * 有害生物信息维护保存图片
     */
     function saveImage()
    {
        // 查询权限
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        // 保存图片参数验证器
        $result = $this->validate($data, 'Pests.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //检测是否有这个id
        $plant = PestsDb::query($data['id']);
        if (!$plant[0]) Helper::reJson($plant);
        //看数据中已经有多少张图片了，最多允许6张,最大2M
        $imageCount = PestsDb::queryImageCount($data['id']);
        if (is_string($imageCount)) return Helper::reJson([false ,$imageCount]);
        if ($imageCount > 5) return Helper::reJson(Errors::IMAGE_COUNT_ERROR);
        $image = request()->file('image');
        if (empty($image)) return Helper::reJson(Errors::IMAGE_NOT_FIND);
        if (!$image->checkImg()) return Helper::reJson(Errors::FILE_TYPE_ERROR);
        if (!$image->checkSize(2 * 1024 * 1024)) return Helper::reJson(Errors::MAX_FILE_SIZE);
        //上传
        $preName = DS . 'pest' . DS . 'image_' . $data['id'] . DS .$image->getInfo()['name'];
        $uploadRes = UploadHelper::upload($image, $preName);
        if (!$uploadRes[0]) return Helper::reJson($uploadRes);
        //更新数据库
        $dbRes = PestsDb::saveImage($data['id'], $uploadRes[1]);
        return Helper::reJson($dbRes);
    }

    /*
     * 有害生物信息维护删除图片
     */
    function deleteImage()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Pests.imageId');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //检测是否有这个id
        $plant = PestsDb::query($data['id']);
        if (!$plant[0]) Helper::reJson($plant);
        $dbRes = PestsDb::deleteImage($data['id'], $data['imageId']);
        return Helper::reJson($dbRes);
    }

}