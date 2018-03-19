<?php
/**
 * Created by 7Long.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 19:39
 */

namespace app\improve\model;

use think\Db;
use think\Exception;
use app\improve\controller\Errors;
use app\improve\controller\Helper;
use app\improve\controller\UploadHelper;

/*
 * 固定标准地查询DB
 */
class SamplePlotSurveyDb extends BaseDb
{
    // 添加记录
    static function add($data,$images)
    {
        try {
            unset($data['id']);
            Db::startTrans();
            $dbRes = Db::table('b_sample_plot_survey')->insertGetId($data);
            if ($dbRes < 1) return Errors::ADD_ERROR;
            if (!empty($images)) {
                if (count($images) > 6) return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $path = UploadHelper::uplodImage($image,DS . 'sample_plot_survey' . DS . 'image_' . $dbRes);
                    if ($path[0] !== true) return $path;
                    $a = Db::table('b_sample_plot_survey_image')->insert(['sample_plot_id'=>$dbRes, 'path'=>$path[1][1]]);
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

    // 编辑
    static function edit($data, $images)
    {
        try {
            $paths = [];
            Db::startTrans();
            if (Helper::lsWhere($data,'del_images')){
                $del_images = $data['del_images'];
                $paths = Db::table('b_sample_plot_survey_image')->field('path')->where('sample_plot_id', $data['id'])->whereIn('id',$del_images)->select();
                if (count($paths) !== count($del_images)) return Errors::NO_IMAGES_DELETED;
                $delRes = Db::table('b_sample_plot_survey_image')->whereIn('id',$del_images)->delete();
                if ($delRes !== count($del_images)) return Errors::DELETE_ERROR;
            }
            unset($data['del_images']);
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('b_sample_plot_survey')->field('sample_plot_id, hazard_type, pest_id, plant_id, generation, happen_tense,
            happen_level, hazard_level, pests_density, monitor_area, happen_area, distrib_area, plant_cover_degree, dead_tree_num, update_time')->update($data);
            if (!empty($images)) {
                $haveCount = Db::table('b_sample_plot_survey_image')->where('sample_plot_id',$data['id'])->count('*');
                if ($haveCount + count($images) > 6) return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $path = UploadHelper::uplodImage($image, DS . 'sample_plot_survey' . DS . 'image_' .$data['id']);
                    if (!is_array($path)) throw new Exception($path);
                    $a = Db::table('b_sample_plot_survey_image')->insert(['sample_plot_id' => $data['id'], 'path' => $path[1][1]]);
                    if ($a < 1) return Errors::IMAGES_INSERT_ERROR;
                }
            }
            Db::commit();
            if (!empty($paths)) foreach ($paths as $path) Helper::deleteFile($path['path']);
            return $dbRes == 1 ? [true , $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    static function uplodImage($image, $id){
        $a = Helper::checkImage(self::queryImageCount($id), $image);
        if (true !== $a) return $a;
        $preName = DS . 'sample_plot_survey' . DS . 'image_'.$id . DS . $image->getInfo()['name'];
        return UploadHelper::upload($image, $preName);
    }

    static function queryImageCount($id)
    {
        try {
            return $dbRes = Db::table('b_sample_plot_survey_image')
                ->where('sample_plot_id', $id)
                ->field('path')->count('*');
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 根据id查看
    static function query($data)
    {
        try {
            $dbRes = Db::table('b_sample_plot_survey')->alias('sps')->where('sps.id', $data['id'])
                ->join('b_pests p', 'p.id = sps.pest_id', 'left')
                ->join('b_plant plant', 'plant.id = sps.plant_id', 'left')
                ->join('b_regularly reg', 'reg.number = sps.sample_plot_id', 'left')
                ->join('c_region r', 'r.id = reg.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('u_user u', 'u.uid = sps.adder', 'left')
                ->field('sps.*, p.cn_name pest_name, plant.cn_name plant_name,u.name surveyer, reg.positions, reg.position_type,
                 r.name r1,r2.name r2,r3.name r3')
                ->find();
            if (empty($dbRes)) return Errors::DATA_NOT_FIND;
            $dbRes['images'] = Db::table('b_sample_plot_survey_image')->where('sample_plot_id', $data['id'])->select();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryInfo($data){
        try {
            $dbRes = Db::table('b_regularly')->alias('reg')->where('reg.number', $data['number'])
                ->join('b_pests p', 'p.id = reg.pests', 'left')
                ->join('b_plant plant', 'plant.id = reg.plant', 'left')
                ->join('c_region r1', 'r1.id = reg.region', 'left')
                ->join('c_region r2', 'r1.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->field('reg.*, p.cn_name pest_name, plant.cn_name plant_name, r1.name r1,r2.name r2,r3.name r3')
                ->find();
            return empty($dbRes) ? Errors::DATA_NOT_FIND : [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //条件查询
    static function ls($data, $sample)
    {
        try {
            $query = Db::table('b_sample_plot_survey')->alias('sps');
            if (Helper::lsWhere($data, 'sample_plot_id')) $query->where('sps.sample_plot_id', $data['sample_plot_id']);
            if (Helper::lsWhere($data, 'pest_id')) $query->where('sps.pest_id', $data['pest_id']);
            if (Helper::lsWhere($data, 'surveyer')) {
                $query->join('u_user u', 'u.uid = sps.adder', 'left')
                    ->whereLike('u.name', '%' . $data['surveyer'] . '%');
            }
            if (Helper::lsWhere($data, 'start_time')) $query->where('sps.create_time', '>=', $data['start_time']);
            if (Helper::lsWhere($data, 'end_time')) $query->where('sps.create_time', '<=', $data['end_time']);
            $query->join('b_pests p', 'p.id = sps.pest_id', 'left')
                ->join('u_user u1', 'u1.uid = sps.adder', 'left');
            if ($sample) {
                $query->field('sps.id, sps.sample_plot_id, sps.pest_id, p.cn_name pest_name, u1.name surveyer, sps.create_time, sps.happen_level');
            } else {
                $query->join('b_plant plant', 'plant.id = sps.plant_id', 'left')
                    ->field('sps.id, sps.sample_plot_id, sps.hazard_type, sps.pest_id, p.cn_name pest_name, sps.generation, 
                sps.happen_tense, sps.happen_level, sps.create_time, u1.name surveyer');
            }
            $query->order('sps.update_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function sampleLs(){
        try {
            $dataRes = Db::table('b_regularly')->field('number')->select();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function delete($ids, $suid){
        try {
            $ret = [];
            foreach ($ids as $id) {
                $adder = Helper::queryAdder($id, "b_sample_plot_survey");
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
                $res = Db::table('b_sample_plot_survey')->where('id', $id)->delete();
                array_push($ret, $res === 1 ?  ['id' => $id, 'res' => 'delete success'] : [ Errors::DELETE_ERROR[1][0], Errors::DELETE_ERROR[1][1] , $id]);
            }
//            return [true , $ret];
            return [true , $ret];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}