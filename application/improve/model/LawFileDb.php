<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22 0022
 * Time: 14:41
 */
namespace app\improve\model;
use app\improve\controller\Errors;
use app\improve\controller\Helper;
use Exception;
use think\Db;
class LawFileDb
{
    static function add($data)
    {
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $result = Db::table('b_law_file')->insertGetId($data);
            return is_numeric($result) ? [true ,$result] : Errors::ADD_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function ls($data){
        try{
            $query = Db::table("b_law_file")->alias('vh');
            if (Helper::lsWhere($data, 'sort')) $query->where('vh.sort', $data['sort']);
            if (Helper::lsWhere($data, 'adder')) {
                $adder = Db::table("u_user")->where('name',$data['adder'])->field('uid')->find();
                $query->where('vh.adder', $adder['uid']);
            }
            if (Helper::lsWhere($data, 'create_time_min')) $query->where('vh.create_time', '>=', $data['create_time_min']);
            if (Helper::lsWhere($data, 'create_time_max')) $query->where('vh.create_time', '<=', $data['create_time_max']);
            $query->join('u_user m', 'm.uid = vh.adder', 'left');
            $query->field('vh.id,vh.create_time,vh.title,vh.sort,m.name');
            $query->order('vh.create_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        }catch (Exception $e){
            return Errors::Error($e->getMessage());
        }
    }

    static function query($id){
        try{
            $query = Db::table('b_law_file')->alias('vh')->where('vh.id', $id)
                ->join('u_user p', 'p.uid = vh.adder', 'left')
                ->field('vh.*,p.name')
                ->find();
            return is_array($query) ? [true, $query] : Errors::DATA_NOT_FIND;
        }catch (Exception $e){
            return Errors::Error($e->getMessage());
        }
    }

    static function edit($data){
        try {
            unset($data['create_time'], $data['adder']);
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('b_law_file')->update($data);
            return $dbRes == 1 ? [true, $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
    static function deleteChecked($ids,$suid){
        try{
            $ret = [];
            foreach ($ids as $id) {
                $adder = Helper::queryAdder($id, "b_law_file");
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
                $dbRes = Db::table('b_law_file')->where('id', $id)->field('file_path')->find();
                $file = is_file('file'.DS.$dbRes['file_path']);
                if (!$file) {
                    array_push($ret ,[ Errors::NO_FILE[1][0], Errors::NO_FILE[1][1],  $id]);
                    continue;
                }
                $res = Db::table('b_law_file')->where('id',$id)->delete();
                if (!$res == 1){
                    array_push($ret, [ Errors::DELETE_ERROR[1][0], Errors::DELETE_ERROR[1][1],  $id]);
                    continue;
                }
                unlink('file'.DS.$dbRes['file_path']);
//                unlink(iconv('UTF-8', 'GB2312', 'file'.DS.$dbRes['file_path']));
                array_push($ret, ['id' => $id ,'res' => 'delete success','file' => $dbRes['file_path']]);
            }
            return [true ,$ret];
        }catch (Exception $e){
            return Errors::Error($e->getMessage());
        }
    }
}