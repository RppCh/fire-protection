<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use Exception;
use think\Db;

/**
 * 病虫害查询
 * Created by xwpeng.
 */
class PestsDb
{
    static function ls($data,$sample = false)
    {
        try {
            $query = Db::table("b_pests");
            if (Helper::lsWhere($data, 'is_localed'))$query ->where('is_localed', $data['is_localed']);
            if (Helper::lsWhere($data, 'name')) $query ->where('cn_name', $data['name']);
            if (Helper::lsWhere($data, 'type')) $query ->where('type', $data['type']);
            if($sample) $query->field('id,cn_name');
            $query->order("update_time", 'desc');
            $dataRes  = $query->paginate($data['per_page'],false,['page'=>$data['current_page']])->toArray();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function local($ids)
    {
        try {
            $ret = [];
            foreach ($ids as $id) {
                $res = Db::table('b_pests')->where('id', $id)->column('id');
                if (empty($res)) array_push($ret, ['id' => $id, 'res' => 'id not find']);
                else {
                    $res = Db::table('b_pests')->where('id', $id)->update(['is_localed' => 1, 'update_time' => date('Y-m-d H:i:s', time())]);
                    array_push($ret, ['id' => $id, 'res' => $res]);
                }
            }
            return [true , $ret];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function edit($data)
    {
        try {
            Db::startTrans();
            $u = [
                "id"=>$data['id'],
                "introduce"=>$data['introduce'],
                "danger_attributes"=>$data['danger_attributes'],
                "update_time"=>date('Y-m-d H:i:s'),
            ];
            if ($data['attach'] == -1) {
                //删附件
//                Helper::deleteFile('plant/attach_'.$data['id']);
                $u['attach'] = null;
                $u['attach_size'] = null;
            }
            Db::table('b_pests')->update($u);
            Db::commit();
            return [true ,$data['id']];
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    static function edit2($data)
    {
        try {
         $dbRes =  Db::table("b_pests")->update($data);
         return $dbRes == 1 ? [true , $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    static function query($id)
    {
        try {
            $plant = Db::table('b_pests')
                ->where('id', $id)
                ->where('is_localed', 1)
                ->select()[0];
            if (empty($plant)) return Errors::DATA_NOT_FIND;
            $plant['images'] = Db::table('b_pest_image')->where('b_pests_id', $id)->select();
            return [true, $plant];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryImageCount($id)
    {
        try {
            return $dbRes = Db::table('b_pest_image')
                ->where('b_pests_id',$id)
                ->field('path')->count('*');
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryAttachPath($id)
    {
        try {
            $plant = Db::table('b_pests')
                ->where('id', $id)
                ->where('is_localed', 1)
                ->column('id,attach');
            return !empty($plant) ? [true , $plant] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function deleteImage($id, $imageId)
    {
        try {
            $dbRes = Db::table('b_pest_image')
                ->where('id', $imageId)
                ->where('b_pests_id', $id)
                ->delete();
            return $dbRes === 1 ? [true , 1] : Errors::DELETE_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function saveImage($id, $path)
    {
        try {
            $dbRes = Db::table('b_pest_image')
                ->insertGetId([
                    'b_pests_id'=>$id,
                    'path'=>$path,
                ]);
            return $dbRes > 0 ? [true,[ $dbRes , $path ]] : Errors::ADD_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

}