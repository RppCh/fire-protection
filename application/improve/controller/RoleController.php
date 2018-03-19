<?php
/**
 * Created by xwpeng.
 * Date: 2017/11/25
 * 用户接口
 */

namespace app\improve\controller;

use app\improve\model\RoleDb;
use think\Controller;
use think\Exception;

class RoleController extends Controller
{

    private function add()
    {
        $auth = Helper::auth([1]);
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Role.add');
        if (true !== $result) return Helper::reErrorJson($result);
        $dbRes = RoleDb::add($data);
        if (is_int($dbRes)) return Helper::reSokJson();
        return Helper::reErrorJson($dbRes);
    }

    private function delete()
    {
        $auth = Helper::auth([1]);
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Role.delete');
        if (true !== $result) return Helper::reErrorJson($result);
        $dbRes = RoleDb::delete($data['rid']);
        if (is_int($dbRes)) return $dbRes > 0 ? Helper::reSokJson() : Helper::reErrorJson("rid no exists");
        return Helper::reErrorJson($dbRes);
    }

    private function edit()
    {
        $auth = Helper::auth([1]);
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Role.edit');
        if (true !== $result) return Helper::reErrorJson($result);
        $dbRes = RoleDb::edit($data);
        if ($dbRes === 1) return Helper::reSokJson();
        return Helper::reErrorJson($dbRes);
    }

    /**
     * 单查
     */
    private function query()
    {
        $auth = Helper::auth([1]);
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Role.query');
        if (true !== $result) return Helper::reErrorJson($result);
        $dbRes = RoleDb::query($data['rid']);
        if (is_array($dbRes)) return Helper::reSokJson($dbRes);
        return Helper::reErrorJson($dbRes);
    }

    /**
     *支持分页
     */
    private function ls()
    {
        $auth = Helper::auth([1]);
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Role.ls');
        if (true !== $result) return Helper::reErrorJson($result);
        $dbRes = RoleDb::ls($data);
        if (is_array($dbRes)) return Helper::reSokJson($dbRes);
        return Helper::reErrorJson($dbRes);
    }

   private function lsPermission()
    {
        $auth = Helper::auth([1]);
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $dbRes = RoleDb::lsPermission();
        if (is_array($dbRes)) return Helper::reSokJson($dbRes);
        return Helper::reErrorJson($dbRes);
    }

}

?>