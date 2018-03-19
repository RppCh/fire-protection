<?php
/**
 * Created by xwpeng.
 * Date: 2017/11/25
 * 用户接口
 */

namespace app\improve\controller;

use app\improve\model\CommonDb;
use app\improve\model\UserDb;
use think\Controller;
use think\Validate;


class UserController extends Controller
{

    function add(){
        return Helper::reJson($this->addImpl());
    }

    private function addImpl()
    {
        $auth = Helper::auth([1]);
        if ($auth[0] !== true) return $auth;
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.add');
        if ($result !== true) return Errors::validateError($result);
        if (!Helper::lsWhere($data, 'pwd')) return Errors::validateError('密码必填');
        $data['uid'] = Helper::uniqStr();
        $data['salt'] = Helper::getRandChar(6);
        $data['pwd'] = md5($data['pwd'] . $data['salt']);
        $dbRes = UserDb::add($data);
        return $dbRes;
    }

    function updateStatus()
    {
        $auth = Helper::auth([1]);
        if ($auth[0] !== true) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.status');
        if ($result !== true) return Helper::reJson(Errors::validateError($result));
        $dbRes = UserDb::updateStatus($data['uid'], $data['status']);
        return Helper::reJson($dbRes);
    }

    function edit(){
        return Helper::reJson($this->editImpl());
    }
    private function editImpl()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return $auth;
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.edit');
        if ($result !== true) return Errors::validateError($result);
        if ($data["uid"] == "9adf8e29ec35844515c5a43938577ac8" && $auth[1]["s_uid"] != "9adf8e29ec35844515c5a43938577ac8")
            return [ false  , ["超级用户只能超级用户编辑"]] ;
        if (Helper::lsWhere($data, "pwd")) {
            $data['salt'] = Helper::getRandChar(6);
            $data['pwd'] = md5($data['pwd'] . $data['salt']);
        } else unset($data['pwd']);
        $dbRes = UserDb::edit($data);
        if (Helper::lsWhere($data, "pwd") && $dbRes[0] ) UserDb::deleteAuth($data['uid']);
        return $dbRes;
    }

    function query()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.query');
        if ($result !== true) return Helper::reJson(Errors::validateError($result));
        $dbRes = UserDb::query($data['uid']);
        return Helper::reJson($dbRes);
    }

    function ls($sample = false)
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new Validate([
            'per_page' => 'require|number|max:500|min:1',
            'current_page' => 'require|number|min:1',
            'dept' => 'number',
            'name' => 'max:16',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::validateError($validate->getError()));
        $dbRes = UserDb::ls($data, $sample);
        return Helper::reJson($dbRes);
    }

    function sampleLs()
    {
        return $this->ls(true);
    }

    function queryRegionUser()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Region.query');
        if ($result !== true) return Helper::reJson(Errors::validateError($result));
        $dbRes = UserDb::queryRegionUser($data['parentId']);
        return Helper::reJson($dbRes);
    }
}

?>