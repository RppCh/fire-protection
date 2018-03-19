<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/14 0014
 * Time: 11:13
 */

namespace app\improve\controller;

use app\improve\model\SyssetNewsDb;
use think\console\command\Help;
use think\Controller;
use think\Request;
use think\Validate;
/*
 * 系统设置新闻系统controller
 */
class SyssetNewsController extends Controller
{
    // 添加
    function add()
    {
        $auth = Helper::auth();
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'SyssetNews.add');
        if (true !== $result) return Helper::reErrorJson($result);
        $data['adder'] = $auth['s_uid'];
        $dbRes = SyssetNewsDb::add($data);
        return $dbRes > 0 ? Helper::reSokJson($dbRes) : Helper::reErrorJson($dbRes);
    }

    // 删除选中
    function deleteChecked()
    {
        $auth = Helper::auth();
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'SyssetNews.ids');
        if (true !== $result) return Helper::reErrorJson($result);
        $dbRes = SyssetNewsDb::deleteChecked($data['ids']);
        return is_array($dbRes) ? Helper::reSokJson(array_values($dbRes)) : Helper::reErrorJson($dbRes);
    }


    // 根据id查看
    function query()
    {
        $auth = Helper::auth();
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'SyssetNews.id');
        if (true !== $result) return Helper::reErrorJson($result);
        $dbRes = SyssetNewsDb::query($data['id']);
        return is_array($dbRes) ? Helper::reSokJson($dbRes) : Helper::reErrorJson($dbRes);
    }

    // 列表
    function ls()
    {
        $auth = Helper::auth();
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data = Helper::getPostJson();
        $validate = new Validate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            'title' =>'max:16',
        ]);
        if (!$validate->check($data)) return Helper::reErrorJson($validate->getError());
        $dbRes = SyssetNewsDb::ls($data);
        if (is_array($dbRes)) return Helper::reSokJson($dbRes);
        return Helper::reErrorJson($dbRes);
    }

    // 编辑
    function edit()
    {
        $auth = Helper::auth();
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        // 验证器
        $data = $_POST;
        $result = $this->validate($data, 'SyssetNews.edit');
        if (true !== $result) return Helper::reErrorJson($result);
        // 权限校验
        $dbRes = SyssetNewsDb::queryAdder($data['id']);
        if (!is_array($dbRes)) return Helper::reErrorJson(Errors::DATA_NOT_FIND);
        //查添加人是不是自己或者自己是管理员
        $isManage = is_array(Helper::auth([1]));
        if (!$isManage) {
            if ($auth['s_uid'] !== $dbRes['adder']) return Helper::reErrorJson('u are not a manager or not an adder');
        }
        // model层操作
        $dbRes = SyssetNewsDb::edit($data);
        return $dbRes === 1 ? Helper::reSokJson() : Helper::reErrorJson($dbRes);
    }
}