<?php
/**
 * Created by PhpStorm.
 * User: LiuTao
 * Date: 2017/12/2/002
 * Time: 16:58
 */

namespace app\improve\controller;

use app\improve\model\PestsDb;
use app\improve\model\TaskDb;
use app\improve\model\VillageHandDb;
use app\improve\validate\BaseValidate;
use app\improve\validate\Pests;
use app\improve\validate\Task;
use app\improve\validate\VillageHand;
use Composer\Util\Git;
use think\Controller;
use think\Error;
use think\Loader;
use think\Validate;

class VillageHandController extends Controller
{

    function add(){
        return Helper::reJson($this->add_());
    }
    private function add_()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return $auth;
        $data = $_POST;
        $result = $this->validate($data, 'VillageHand.add');
        if ($result !== true) return Errors::Error($result);
        $data['adder'] = $auth[1]['s_uid'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = $data['create_time'];
        $images = request()->file("images");
        return VillageHandDb::add($data, $images);
    }


    function ls($sample = false)
    {
        return Helper::reJson($this->lsDb($sample));
    }

    private function lsDb($sample = false, $download = false)
    {
        $auth = Helper::auth();
        if (!$auth[0]) return $auth;
        $data = $download ? $_GET : Helper::getPostJson();
        $data['uid']  = $auth[1]['s_uid'];
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'pest_id' => 'number',
            'hazard_type' => 'in:1,2,3,4',
            'hand_time_min' => 'dateFormat:Y-m-d H:i:s',
            'hand_time_max' => 'dateFormat:Y-m-d H:i:s',
            'region' => 'max:20|region',
            'position_type' => 'number|in:-1,1,2,3',
            'adder_name' => 'max:16',
        ]);
        return $validate->check($data) ? VillageHandDb::ls($data, $sample) : Errors::Error($validate->getError());
    }

    function sampleLs()
    {
       return $this->ls(true);
    }

    function query()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'VillageHand.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = VillageHandDb::query($data['id']);
        return Helper::reJson($dbRes);
    }

    function edit()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_POST;
        $result = $this->validate($data, 'VillageHand.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $adder = Helper::queryAdder($data['id'], "b_village_hand");
        if (!$adder[0])  return Helper::reJson(Errors::DATA_NOT_FIND);
        $a = Helper::checkAdderOrManage($adder, $auth[1]['s_uid']);
        if (!$a) return Helper::reJson(Errors::LIMITED_AUTHORITY);
        $images = request()->file("images");
        $dbRes = VillageHandDb::edit($data, $images);
        return Helper::reJson($dbRes);
    }

    // 删除选中
    function deleteChecked()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'VillageHand.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = VillageHandDb::deleteChecked($data['ids'], $auth[1]['s_uid']);
        return Helper::reJson($dbRes);
    }

    function exportExcel()
    {
        $result = $this->lsDb(false, true);
        if (!$result[0]) return ;
        $result = $this->exportDataHandle($result[1]);
        $name = '病虫害防治记录';
        $header = ['区域', '危害类型', '病虫种类', '防治方法', '防治时间', '上报人'];
        excelExport($name, $header, $result);
    }

    private function exportDataHandle($result)
    {
        $dataRes = $result['data'];
        foreach ($dataRes as $key => $value) {
            unset($dataRes[$key]['id'], $dataRes[$key]['pest_id']);
            $a['region'] = $dataRes[$key]['r2'] . $dataRes[$key]['r1'];
            $dataRes[$key] = $a + $dataRes[$key];
            unset($dataRes[$key]['r2'], $dataRes[$key]['r1']);
            foreach ($value as $k => $v) {
                $h = [1=>"病害",2 => "虫害", 3 => "鼠害", 4 => "有害植物",];
                $m = ["生物防治", "物理防治", "化学防治","人工防治"];
                if ($k == 'hazard_type') $dataRes[$key][$k] = $h[$v];
                if ($k == 'hand_method') $dataRes[$key][$k] = $m[$v - 1];
            }
        }
        return $dataRes;
    }

    function getPestsType()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $dbRes = VillageHandDb::getPestsType();
        return Helper::reJson($dbRes);
    }

    function messageChart()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'region' => 'require|max:20|region',
            'pest_id' => 'require|number',
            'start_time' => 'require|dateFormat:Y-m',
            'end_time' => 'require|dateFormat:Y-m',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = VillageHandDb::messageChart($data);
        return Helper::reJson($result);
    }


    function statisticslist(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:500|min:1|notin:0',
            'current_page' => 'require|number|min:1|notin:0',
            'region' => 'require|max:20|region',
            'pest_id' => 'require|number',
            'survey_time_min' => 'dateFormat:Y-m',
            'survey_time_max' => 'dateFormat:Y-m',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = VillageHandDb::villagesList($data);
        return Helper::reJson($result);
    }

    function statisticsexcel()
    {
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
        $data = $_GET;
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:500|min:1|notin:0',
            'current_page' => 'require|number|min:1|notin:0',
            'region' => 'require|max:20|region',
            'pest_id' => 'number',
            'survey_time_min' => 'dateFormat:Y-m',
            'survey_time_max' => 'dateFormat:Y-m',
        ]);
        if (!$validate->check($data)) return ;
        $result = VillageHandDb::villagesList($data);
        if (!$result[0]) return;
        $name = $result[1]['title'];
        $header = ['区域', '病虫种类', '发生面积(亩)', '防治面积(亩)', '防治费用(元)','挽回灾害面积(亩)','防治次数'];
        excelExport($name, $header, $result[1]['data']);
    }

    function pestList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $result = VillageHandDb::pestList();
        return Helper::reJson($result);
    }
}