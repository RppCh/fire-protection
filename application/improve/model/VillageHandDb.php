<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use app\improve\controller\UploadHelper;
use Exception;
use think\Db;
use think\Error;
use think\Model;

/**
 * 病虫害防治
 * Created by xwpeng.
 */
class VillageHandDb
{

    static function add($data, $images)
    {
        try {
            Db::startTrans();
            $dbRes = Db::table('b_village_hand')->insertGetId($data);
            if ($dbRes < 1) return Errors::ADD_ERROR;
            if (!empty($images)) {
                if (count($images) > 6) return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $path = UploadHelper::uplodImage($image, DS . 'village_hand' . DS . 'image_' . $dbRes);
                    if ($path[0] !== true) return $path;
                    $a = Db::table('b_village_hand_image')->insert(['village_hand_id' => $dbRes, 'path' => $path[1][1]]);
                    if ($a < 1) return Errors::IMAGES_INSERT_ERROR;
                }
            }
            Db::commit();
            return [true, $dbRes];
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    static function ls($data, $sample = false)
    {
        try {
            $query = Db::table("b_village_hand")->alias('vh');
            if (Helper::lsWhere($data, 'hazard_type')) $query->where('vh.hazard_type', $data['hazard_type']);
            if (Helper::lsWhere($data, 'region')) $query->whereLike('vh.region', $data['region'] . '%');
            if (Helper::lsWhere($data, 'pest_id')) $query->where('vh.pest_id', $data['pest_id']);
            if (Helper::lsWhere($data, 'hand_time_min')) $query->where('vh.create_time', '>=', $data['hand_time_min']);
            if (Helper::lsWhere($data, 'hand_time_max')) $query->where('vh.create_time', '<=', $data['hand_time_max']);
            if (Helper::lsWhere($data, 'position_type')) $query->where('vh.position_type', $data['position_type']);
            if (Helper::lsWhere($data, 'adder_name')) {
                $query->view('u_user u3', 'name adder_name', "uid = vh.adder");
                $query->whereLike('u3.name', '%' . $data['adder_name'] . '%');
            }
            if ($sample) {
                $query->field('vh.id,vh.create_time, vh.hazard_type, vh.hand_method, vh.positions');
            } else {
                $query->join('b_pests p', 'p.id = vh.pest_id', 'left');
                $query->join('c_region r', 'r.id = vh.region', 'left');
                $query->join('c_region r2', 'r.parentId = r2.id', 'left');
                $query->join('u_user u', 'u.uid = vh.adder', 'left');
                $query->field('vh.id,r.name r1,r2.name r2, vh.hazard_type, p.cn_name pest_name,vh.pest_id, vh.hand_method,vh.create_time, u.name adder_name');
            }
            $query->order('vh.update_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function query($id)
    {
        try {
            $hand = Db::table('b_village_hand')->alias('vh')->where('vh.id', $id)
                ->join('b_pests p', 'p.id = vh.pest_id', 'left')
                ->join('c_region r', 'r.id = vh.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('u_user u', 'u.uid = vh.adder', 'left')
                ->field('vh.*, r.name r1, r2.name r2, r3.name r3, p.cn_name pest_name, u.name adder_name')
                ->find();
            if (empty($hand)) return Errors::DATA_NOT_FIND;
            $hand['images'] = Db::table('b_village_hand_image')->where('village_hand_id', $id)->select();
            return [true, $hand];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function edit($data, $images)
    {
        try {
            $paths = [];
            Db::startTrans();
            if (Helper::lsWhere($data, 'del_images')) {
                $del_images = $data['del_images'];
                $paths = Db::table('b_village_hand_image')->field('path')->where('village_hand_id', $data['id'])->whereIn('id', $del_images)->select();
                if (count($paths) !== count($del_images)) return Errors::NO_IMAGES_DELETED;
                $delRes = Db::table('b_village_hand_image')->whereIn('id', $del_images)->delete();
                if ($delRes !== count($del_images)) return Errors::DELETE_ERROR;
            }
            unset($data['del_images']);
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('b_village_hand')
                ->field("region, hazard_type,happen_time, 
             hand_method, drug_amount, hand_cost, hand_area,
             happen_area, hand_effect, save_pest_area, positions,
             position_type, id,drug_name,update_time,pest_id")
                ->update($data);
            //图片上传
            if (!empty($images)) {
                //数量判断
                $haveCount = Db::table('b_village_hand_image')->where('village_hand_id', $data['id'])->count('*');
                if ($haveCount + count($images) > 6) return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $path = UploadHelper::uplodImage($image, DS . 'village_hand' . DS . 'image_' . $data['id']);
                    if (!$path[0]) return [false, $path];
                    $a = Db::table('b_village_hand_image')->insert(['village_hand_id' => $data['id'], 'path' => $path[0]]);
                    if ($a < 1) return Errors::IMAGES_INSERT_ERROR;
                }
            }
            Db::commit();
            //物理
            if (!empty($paths)) foreach ($paths as $path) Helper::deleteFile($path['path']);
            return $dbRes == 1 ? [true , $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    // 删除选中
    static function deleteChecked($ids, $suid)
    {
        try {
            $ret = [];
            foreach ($ids as $id) {
                $adder = Helper::queryAdder($id, "b_village_hand");
                if (!$adder[0]) {
                    $adder[1][3] = $id;
                    array_push($ret, $adder[1]);
                    continue;
                }
                $adder = Helper::checkAdderOrManage($adder, $suid);
                if (!$adder[0]) {
                    $adder[1][3] = $id;
                    array_push($ret, $adder[1]);
                    continue;
                }
                $res = Db::table('b_village_hand')->where('id', $id)->delete();
                array_push($ret, $res === 1 ?  ['id' => $id, 'res' => 'delete success'] : [ Errors::DELETE_ERROR[1][0], Errors::DELETE_ERROR[1][1] , $id]);
            }
            return [true , $ret];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function getPestsType()
    {
        try {
            $dbRes = Db::table('b_village_hand')->alias('vh')
                ->join('b_pests p', 'p.id = vh.pest_id', 'left')
                ->field('DISTINCT p.cn_name,vh.pest_id')->select();
            return !empty($dbRes) ? [true , $dbRes] : Errors::DATA_NOT_FIND;

        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function messageChart($data)
    {
        try {
            $query = Db::table('b_village_hand')->alias('vh')
                ->join('c_region r1', 'r1.id = vh.region', 'left')
                ->where('vh.pest_id', '=', $data['pest_id'])
                ->whereLike('vh.region', $data['region'] . '%')
                ->where("vh.create_time", '>=', $data['start_time'])
                ->where('vh.create_time', '<=', date("Y-m", strtotime($data['end_time'] . '+1 month')))
                ->group('DATE_FORMAT(vh.create_time, "%Y-%m")')
                ->field("SUM(vh.happen_area) '发生面积(亩)', SUM(vh.hand_area) '防治面积(亩)', SUM(vh.save_pest_area) '挽回灾害面积(亩)',
	                        DATE_FORMAT(vh.create_time, '%Y-%m') '年月', COUNT(*) '防治次数(次)'");
            $dbRes = $query->select();
            return !empty($dbRes) ? [true , $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


    /**
     * @param $data
     * @param bool $say
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function villagesListSon($data , $overallSum = false)
    {
        $a = ' p.cn_name pest, sum(vh.happen_area) sum_happen,sum(vh.hand_area) sum_hand_area,sum(vh.hand_cost) sum_hand_cost,sum(vh.save_pest_area) sum_save_pest_area,sum(vh.hand_cost) sum_hand_cost ,count(vh.id) sum';
        $b = 'left(vh.region,9) regions, ';
        $c = 'count(distinct left(vh.region,9)) regions, ';
        $query = Db::table('b_village_hand')->alias('vh')
                ->where('vh.pest_id', $data['pest_id'])
                ->whereLike('vh.region', $data['region'] . '%');
        if (Helper::lsWhere($data, 'hand_time_min')) $query->where('vh.create_time', '>=', $data['hand_time_min']);
        if (Helper::lsWhere($data, 'hand_time_max')) $query->where('vh.create_time', '<=', $data['hand_time_max']);
        $query->join('b_pests p', 'p.id = vh.pest_id', 'left');
        if ($overallSum){
            $db = $query  ->field($b.$a)->group('left(vh.region,9)')
                    ->paginate($data['per_page'], false, ['page' => $data['current_page']])
                    ->toArray();
            foreach ($db["data"] as $key => $value){
                $db["data"][$key]['regions'] = Db::table('c_region')->where('id',$value['regions'])->field('name')->find()['name'];
            }
        }
        else{
            $db  =$query ->field($c.$a)->group('left(vh.region,6)')->find();
        }
        return $db;
    }

    static function villagesList($data)
    {
        try {
            $pests = '';
            $dataResOne = VillageHandDb::villagesListSon($data);
            $dataResTwo = VillageHandDb::villagesListSon($data, true);
            if (empty($dataResTwo['data'])) return Errors::DATA_NOT_FIND;
            $bbc = [];
            foreach ($dataResOne as $key => $value) {
                switch ($key) {
                    case "regions":
                        $bbc[0][$key] = "防治镇数:" . $value . "个";
                        break;
                    case "pest":
                        $bbc[0][$key] = "病虫名:" . $value . "种";
                        break;
                    case "sum_happen":
                        $bbc[0][$key] = "发生面积总数:" . $value . "亩";
                        break;
                    case "sum_hand_area":
                        $bbc[0][$key] = "防治面积总数:" . $value . "亩";
                        break;
                    case "sum_hand_cost":
                        $bbc[0][$key] = "总防治费用:" . $value . "元";
                        break;
                    case "sum_save_pest_area":
                        $bbc[0][$key] = "总挽回灾害面积:" . $value . "亩";
                        break;
                    case "sum":
                        $bbc[0][$key] = "防治:" . $value . "次";
                        break;
                }
            }
            $dataResTwo['data'] = array_merge_recursive($bbc, $dataResTwo['data']);
            $dataResTwo['title'] = "新宁县" . $dataResOne['pest'] . "防治记录统计(总共防治" . $dataResTwo['data'][0]['sum'] . "，" . $dataResTwo['data'][0]['regions'] . ")";
            return !empty($dataResTwo) ? [true , $dataResTwo] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }

    }

    static function pestList()
    {
        try {
            $query = Db::table('b_village_hand')->alias('vh');
            $query->join('b_pests p', 'p.id = vh.pest_id', 'left');
            $dataRes = $query->field('p.cn_name label,vh.pest_id value')->group('p.cn_name,vh.pest_id')->order('vh.pest_id', 'desc')->select();
            return !empty($dataRes) ? [true , $dataRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

}