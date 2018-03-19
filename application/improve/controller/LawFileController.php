<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22 0022
 * Time: 10:04
 */

namespace app\improve\controller;

use app\improve\model\LawFileDb;
use app\improve\validate\Pests;
use think\Controller;
use think\Validate;
use app\improve\validate\BaseValidate;

class LawFileController extends Controller
{
    public function add()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        unset($data['submit']);
        $result = $this->validate($data, 'LawFile.add');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $data['adder'] = $auth[1]['s_uid'];
        $attach = request()->file('attach');
        if (empty($attach)) return Helper::reJson(Errors::NO_FILE);
        if (!$attach->checkSize(100 * 1024 * 1024)) return Helper::reJson(Errors::MAX_FILE_SIZE);
        $name = $attach->getInfo()['name'];
        $preName = DS . 'law' . DS . 'attach_' . $data['adder'] . DS . $name;
        $result = UploadHelper::upload($attach, $preName);
        if (!$result[0]) return Helper::reJson($result);
        $data['file_path'] = $result[1];
        $data['file_name'] = $name;
        $results = LawFileDb::add($data);
        if ($results[0]){
             $results[1] =[$results[1] ,$result[1]];
        }
        else{
            unlink(iconv('UTF-8', 'GB2312', 'file' . DS . $data['file_path']));
        }
        return Helper::reJson($results);
    }

    public function ls()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'create_time_min|最小发布时间' => 'dateFormat:Y-m-d H:i:s',
            'create_time_max|最大发布时间' => 'dateFormat:Y-m-d H:i:s',
            'per_page|每页数' => 'require|number',
            'current_page|当前页' => 'require|number',
            'adder|发布人' => 'max:32',
            'sort|类别' => 'in:1,2',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $results = LawFileDb::ls($data);
        return Helper::reJson($results);
    }

    public function edit()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        unset($data['submit']);
        $result = $this->validate($data, 'LawFile.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $attach = request()->file('attach');
        if (!empty($attach)) {
            $name = $attach->getInfo()['name'];
            if (!$attach->checkSize(100 * 1024 * 1024)) return Helper::reJson(Errors::MAX_FILE_SIZE);
            $preName = DS . 'law' . DS . 'attach_' . $data['adder'] . DS . $name;
            $uploadRes = UploadHelper::upload($attach, $preName);
            if ($uploadRes[0]){
                unlink(iconv('UTF-8', 'GB2312', 'file' . DS . $data['file_path']));
            }
            else{
                return Helper::reJson($uploadRes);
            }
            $data['file_path'] = $uploadRes[1];
            $data['file_name'] = $name;
        }
        $dbRes = LawFileDb::edit($data);
        return Helper::reJson($dbRes);
        }

    public function query()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'LawFile.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = LawFileDb::query($data['id']);
        if ( $dbRes) $dbRes[1]['file_size'] = Helper::sizecount(filesize('file' . DS . $dbRes[1]['file_path']));
//        $dbRes['file_size'] = Helper::sizecount(filesize(iconv('UTF-8', 'GB2312', 'file' . DS . $dbRes[1]['file_path'])));
        return Helper::reJson($dbRes);
    }

    public function delete()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'LawFile.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = LawFileDb::deleteChecked($data['ids'],$auth[1]['s_uid']);
        return Helper::reJson($dbRes);
    }
}