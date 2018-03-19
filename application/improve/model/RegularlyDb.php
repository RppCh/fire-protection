<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 14:34
 */
namespace app\improve\model;
use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
class RegularlyDb extends BaseDb{
    static function add($data)
    {
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            unset($data['id']);
            $result = Db::table('b_regularly')->insertGetId($data);
            return is_numeric($result) ? [true ,$result] : Errors::ADD_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function ls($data)
    {
        try {
            $query = Db::table('b_regularly')->alias('plp');
            if (Helper::lsWhere($data, 'region')) $query->whereLike('plp.region', $data['region'] . '%');
            if (Helper::lsWhere($data, 'pests')) $query->where('plp.pests', $data['pests']);
            if (Helper::lsWhere($data, 'plant')) $query->where('plp.plant', $data['plant']);
            $query->where('plp.state', 2);
            $query->join('c_region r', 'r.id = plp.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('b_pests p', 'p.id = plp.pests', 'left')
                ->join('b_plant p2', 'p2.id = plp.plant', 'left')
                ->field('plp.*,r.name r1,r2.name r2,r3.name r3,p.cn_name pests_name,p2.cn_name plant_name')
                ->order('plp.update_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function query($id)
    {
        try {
            $dbRes = Db::table('b_regularly')->alias('plp')->where('plp.id', $id)
                ->where('plp.state', 2)
                ->join('c_region r', 'r.id = plp.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('b_pests p', 'p.id = plp.pests', 'left')
                ->join('b_plant p2', 'p2.id = plp.plant', 'left')
                ->field('plp.*,r.name r1,r2.name r2,r3.name r3,p.cn_name pests_name,p2.cn_name plant_name')
                ->find();
            return is_array($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function edit($data)
    {
        try {
            if (!static::query($data['id'])[0]) return Errors::DATA_NOT_FIND;
            $data['update_time'] =  date('Y-m-d H:i:s');
            unset($data['create_time']);
            $dbRes = Db::table('b_regularly')->field('region,positions,position_type,number,pests,plant,regularly_area,
            representative_area,update_time')->update($data);
            return $dbRes == 1 ? [true, $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function cartogram($data){

        try{
            $run=[];
            foreach ($data['year'] as $year){
                $sql = "
                select  MONTH(a.create_time) as time,COUNT(DISTINCT b.region) AS region_nilsen ,SUM(a.distrib_area) AS distrib_sum from b_sample_plot_survey a LEFT JOIN b_regularly b ON b.number = a.sample_plot_id
                 WHERE a.pest_id = " . $data['pests'] . " and YEAR(a.create_time) = " . $year . "  and left(b.region,6) =  " . substr($data['region'], 0, 6) . "  GROUP BY time
                ";
                $run[$year] = DB::query($sql);
            }
            return empty($run) ? Errors::DATA_NOT_FIND : [true, $run];
        }catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function pestList(){
        try{
            $query = Db::table('b_regularly')->alias('r');
            $query->join('b_pests p', 'p.id = r.pests', 'left')
                  ->where('r.state', 2);
            $dataRes = $query->field('p.cn_name label,r.pests value')->group('p.cn_name,r.pests')->order('r.pests', 'desc')->select();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        }catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function delete($ids){
        try{
            $ret = [];
            foreach ($ids as $id) {
                $query = static::query($id);
                if (!$query[0]) {
                    $query[1][3] = $id;
                    array_push($ret, $query[1]);
                    continue;
                }
//                $res = Db::table('b_regularly')->where('id',$id)->delete();
                $res = Db::table('b_regularly')->update(['state' => 2,'id'=>$id]);
                if (!is_numeric($res)){
                    array_push($ret, [ Errors::DELETE_ERROR[1][0], Errors::DELETE_ERROR[1][1],  $id]);
                    continue;
                }
                array_push($ret, ['id' => $id ,'res' => 'delete success']);
            }
            return [true ,$ret];
        }catch (Exception $e){
            return Errors::Error($e->getMessage());
        }
    }
}