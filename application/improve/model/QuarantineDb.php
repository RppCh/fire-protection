<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 0005
 * Time: 15:57
 */

namespace app\improve\model;

use think\Db;
use app\improve\controller\Errors;
use Exception;
use app\improve\controller\Helper;

class QuarantineDb
{
    static function add( $data )
    {
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $result = Db::table('b_quarantine')->insertGetId($data);
            return is_numeric($result) ? [true ,$result] : Errors::ADD_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function ls ( $data )
    {
        try {
            $query = Db::table('b_quarantine')->alias('bq');
            if (Helper::lsWhere($data, 'region')) $query->where('bq.region', $data['region'] . '%');
            if (Helper::lsWhere($data, 'organization')) $query->whereLike('bq.organization','%' . $data['organization'] . '%');
            $query->join('c_region r', 'r.id = bq.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('u_user u', 'u.uid = bq.adder', 'left')
                ->field('bq.*,r.name r1,r2.name r2,r3.name r3 ,u.name')
                ->order('bq.found_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function query($id)
    {
        try {
            $dbRes = Db::table('b_quarantine')->alias('bq')->where('bq.id', $id)
                ->join('c_region r', 'r.id = bq.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('u_user u', 'u.uid = bq.adder', 'left')
                ->field('bq.*,r.name r1,r2.name r2,r3.name r3,u.name')
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
            $dbRes = Db::table('b_quarantine')->field('region,positions,position_type,organization,
                     found_time,nature,tel,administrator,update_time')->update($data);
            return $dbRes == 1 ? [true, $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function delete($ids)
    {
        try {
            $ret = [];
            foreach ($ids as $id) {
                $adder = Helper::queryAdder($id, "b_quarantine");
                if (!$adder[0]) {
                    $adder[1][3] = $id;
                    array_push($ret, $adder[1]);
                    continue;
                }
                $res = Db::table('b_quarantine')->where('id', $id)->delete();
                array_push($ret, $res === 1 ?  ['id' => $id, 'res' => 'delete success'] : [ Errors::DELETE_ERROR[1][0], Errors::DELETE_ERROR[1][1] , $id]);
            }
            return [true , $ret];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}