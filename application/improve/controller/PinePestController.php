<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/13 0013
 * Time: 10:50
 */

namespace app\improve\controller;

use app\improve\model\PinePestDb;
use app\improve\validate\PinePest;
use app\improve\validate\BaseValidate;
use think\Controller;
use think\Loader;
use think\Validate;

/*
 * 松材线虫病调查Controller
 */
class PinePestController extends Controller
{

    // 根据id查看
    function query()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'PinePest.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PinePestDb::query($data);
        return Helper::reJson($dbRes);
    }

    // 条件查询
    function ls($requestType = true,$sample = false)
    {
        return Helper::reJson($this->lsDb($requestType,$sample));
    }

    function lsDb($requestType = true,$sample = false)
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $requestType ? Helper::getPostJson(): $_GET;
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'region' => 'max:20|region',
            'position_type' => 'number|in:-1,1,2,3',
            'surveryer' => 'max:16',
            'start_time' => 'dateFormat:Y-m-d',
            'end_time' => 'dateFormat:Y-m-d',
        ]);
        return $validate->check($data) ? PinePestDb::ls($data, $sample) : Errors::Error($validate->getError());
    }

    function sampleLs()
    {
        return $this->ls(true,true);
    }

    // 添加
    function add()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'PinePest.add');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $data['adder'] = $auth[1]['s_uid'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = $data['create_time'];
        $images = request()->file("images");
        $result = PinePestDb::add($data, $images);
        return Helper::reJson($result);
    }

    // 编辑
    function edit()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'PinePest.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $adder = PinePestDb::queryAdder($data['id'], "b_pineline_pest");
        if (!$adder[0]) return Helper::reErrorJson($adder);
        $a = Helper::checkAdderOrManage($adder, $auth[1]['s_uid']);
        if (!$a[0]) return Helper::reErrorJson($a);
        $images = request()->file("images");
        $dbRes = PinePestDb::edit($data, $images);
        return Helper::reJson($dbRes);
    }

    // 删除选中
    function deleteChecked()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'PinePest.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PinePestDb::deleteChecked($data['ids'], $auth[1]['s_uid']);
        return Helper::reJson($dbRes);
    }

    function exportExcel()
    {
        $result = $this->lsDb($requestType = false);
        if ($result[0]){
            $result = $this->exportDataHandle($result[1]);
            $name = '松材线虫调查记录表';
            $header = ['区域', '松林面积(亩)', '调查面积(亩)', '枯死松树数(株)', '松材线虫数(条)', '调查人', '调查时间'];
            excelExport($name, $header, $result);
        }
    }

    private function exportDataHandle($result)
    {
        $dataRes = $result['data'];
        foreach ($dataRes as $key => $value) {
            $a['region'] = $dataRes[$key]['r3'].$dataRes[$key]['r2'] . $dataRes[$key]['r1'];
            $dataRes[$key] = $a + $dataRes[$key];
            unset($dataRes[$key]['id'],$dataRes[$key]['r3'],$dataRes[$key]['r2'], $dataRes[$key]['r1']);
        }
        return $dataRes;
    }

    function trendChart()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'region' => 'require|max:20|region',
            'start_time' => 'require|dateFormat:Y-m',
            'end_time' => 'require|dateFormat:Y-m',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = PinePestDb::trendChart($data);
        return Helper::reJson($result);
    }
}