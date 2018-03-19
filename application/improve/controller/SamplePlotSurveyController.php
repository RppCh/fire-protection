<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 17:20
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\validate\BaseValidate;
use app\improve\model\SamplePlotSurveyDb;
/*
 * 固定标准地调查Controller
 */
class SamplePlotSurveyController extends Controller
{
    // 添加
    function add()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'SamplePlotSurvey.add');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $data['adder'] = $auth[1]['s_uid'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = $data['create_time'];
        $images = request()->file("images");
        $result = SamplePlotSurveyDb::add($data,$images);
        return Helper::reJson($result);
    }

    // 查看
    function query()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'SamplePlotSurvey.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = SamplePlotSurveyDb::query($data);
        return Helper::reJson($dbRes);
    }

    function queryInfo()
    {
        $data = Helper::getPostJson();
        $dbRes = SamplePlotSurveyDb::queryInfo($data);
        return Helper::reJson($dbRes);
    }

    //条件查询
    function ls($sample = false)
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:500|min:1',
            'current_page' => 'require|number|min:1',
            'sample_plot_id' => 'number',
            'pest_id' => 'number',
            'surveyer' => 'max:16',
            'start_time' => 'dateFormat:Y-m-d H:i:s',
            'end_time' => 'dateFormat:Y-m-d H:i:s',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = SamplePlotSurveyDb::ls($data,$sample);
        return Helper::reJson($result);
    }

    function androidLs()
    {
        return $this->ls(true);
    }

    function sampleLs()
    {
        $result = SamplePlotSurveyDb::sampleLs();
        return Helper::reJson($result);
    }

    // 编辑
    function edit()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'SamplePlotSurvey.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $adder = Helper::queryAdder($data['id'],"b_sample_plot_survey");
        if (!$adder[0]) return Helper::reJson(Errors::DATA_NOT_FIND);
        $a = Helper::checkAdderOrManage($adder, $auth[1]['s_uid']);
        if (!$a) return Helper::reJson(Errors::LIMITED_AUTHORITY);
        $images =  request()->file("images");
        $dbRes = SamplePlotSurveyDb::edit($data, $images);
        return Helper::reJson($dbRes);
    }

    // 删除选中
    function deleteChecked()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'SamplePlotSurvey.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = SamplePlotSurveyDb::delete($data['ids'], $auth[1]['s_uid']);
        return Helper::reJson($dbRes);
    }
}