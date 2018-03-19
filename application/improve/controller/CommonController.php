<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 13:51
 */

namespace app\improve\controller;


use app\improve\model\CommonDb;
use think\Controller;

class  CommonController extends Controller
{


    public function queryRegion()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Region.query');
        if (true !== $result) return Helper::reJson(Errors::validateError($result));
        $dbRes = CommonDb::queryRegion($data['parentId']);
        if (is_array($dbRes)) return Helper::reJson([true, $dbRes]);
        return Helper::reJson(Errors::Error($dbRes));
    }

    private function addApk()
    {
        return Helper::reJson($this . $this->addApkImpl());
    }

   private function addApkImpl()
    {
        $apk = request()->file('apk');
        if (empty($apk)) return Errors::ATTACH_NOT_FIND;
        if (!$apk->checkSize(100 * 1024 * 1024)) return Errors::MAX_FILE_SIZE;
        $preName = DS . $apk->getInfo()['name'];
        $uploadRes = UploadHelper::upload($apk, $preName);
        return [is_array($uploadRes), is_array($uploadRes) ? $uploadRes[0] : $uploadRes];
    }

}

?>