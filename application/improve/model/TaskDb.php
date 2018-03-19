<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use app\improve\controller\UploadHelper;
use Exception;
use think\Db;

/**
 * 任务管理model
 * Created by xwpeng.
 */
class TaskDb
{

    static function add($data, $images)
    {
        try {
            $assigners = $data['assigner'];
            unset($data['assigner']);
            Db::startTrans();
            $dbRes = Db::table('b_task')->insertGetId($data);
            if ($dbRes < 1)  return Errors::ADD_ERROR;
            foreach ($assigners as $a) {
                $r = Db::table('b_task_assigner')->insert(['tid' => $dbRes, 'uid' => $a]);
                if ($r < 1) return Errors::ADD_ERROR;
            }
            if (!empty($images)) {
                if (count($images) > 6) return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $path = UploadHelper::uplodImage($image, DS . 'task' . DS . 'image_' . $dbRes);
                    if ($path[0] !== true) return $path;
                    $a = Db::table('b_task_image')->insert(['tid' => $dbRes, 'use' => 1, 'path' => $path[1][1]]);
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

    /**
     * 过期处理
     */
    private static function whereStatus($data, $query)
    {
        switch ($data['status']) {
            case -2:
                $query->where('t.deadline', '<', date('Y-m-d H:i:s'));
                $query->where('t.status', '<>', 2);
                break;
            case -3:
                $query->where('t.deadline', '<', date('Y-m-d H:i:s'));
                $query->whereOr('t.status', 2);
                break;
            default:
                $query->where('t.deadline', '>=', date('Y-m-d H:i:s'));
                $query->where('t.status', $data['status']);
                break;
        }
    }

    static function ls($data, $uid)
    {
        try {
            $query = Db::table("b_task")->alias('t');
            if (Helper::lsWhere($data, 'name')) $query->whereLike('t.name', '%' . $data['name'] . '%');
            if (Helper::lsWhere($data, 'type')) $query->where('t.type', $data['type']);
            if (Helper::lsWhere($data, 'status')) self::whereStatus($data, $query);
            if (Helper::lsWhere($data, 'founder_name')){
            $query->view('u_user u3','name',"uid = t.founder");
            $query->whereLike('u3.name','%'.$data['founder_name'].'%');
            }
            //判断是否是管理员
            $auth = Helper::auth([1]);
            if (!is_array($auth)) $query->join('b_task_assigner ta', "t.id = ta.tid")->where('ta.uid', $uid);
            $query->join('u_user u', 'u.uid = t.recevier', 'left');
            $query->join('u_user u1', 'u1.uid = t.founder', 'left');
            $query->field('t.id,t.recevier,u.name recevier_name,t.name,t.type, t.content, t.positions,  t.create_time, t.deadline, t.status,u1.name founder_name, t.founder');
            $query->order('t.update_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function query($id)
    {
        try {
            $task = Db::table('b_task')->where('id', $id)->find();
            if (empty($task)) return Errors::DATA_NOT_FIND;
            $task['founder_name'] = Db::table('u_user')->where('uid', $task['founder'])->column('name')[0];
            if (!empty($task['recevier'])) $task['recevier_name'] = Db::table('u_user')->where('uid', $task['recevier'])->column('name')[0];
            $task['images'] = Db::table('b_task_image')->where('tid', $id)->select();
            $task['assigner'] = Db::table('b_task_assigner')->alias('ta')
                ->where('ta.tid', $id)
                ->join('u_user u', 'u.uid = ta.uid')
                ->field('u.uid,u.name')
                ->select();
            return is_array($task) ? [true, $task] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryPeople($id)
    {
        try {
            $task = Db::table('b_task')->where('id', $id)->field('founder, recevier')->find();
            if (empty($task)) return Errors::DATA_NOT_FIND;
            return $task;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function queryImageCount($id, $use)
    {
        try {
            return Db::table('b_task_image')
                ->where('tid', $id)
                ->where('use', $use)
                ->count('*');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function deleteImage($tid, $use, $id)
    {
        try {
            $dbRes = Db::table('b_task_image')
                ->where('tid', $tid)
                ->where('use', $use)
                ->where('id', $id)
                ->delete();
            return $dbRes;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function saveImage($path)
    {
        try {
            $data = [
                'path' => $path,
            ];
            $image = Db::table('b_task_image')->insertGetId($data);
            return $image > 0 ? [$image] : Errors::INSERT_ERROR;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function edit($data, $images = null)
    {
        try {
            Db::startTrans();
            if (!empty($images))foreach ($images as $image) {
                $path = UploadHelper::uplodImage($image, $data['id']);
                if ($path[0] !== true) return $path;
                $a = Db::table('b_task_image')->insert(['tid' => $data['id'], 'use' => 2, 'path' => $path[1][1]]);
                if ($a < 1) return Errors::IMAGES_INSERT_ERROR;
            }
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('b_task')->update($data);
            if ($dbRes !== 1) return Errors::UPDATE_ERROR;
            Db::commit();
            return [true, $dbRes];
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    // 删除选中
    static function deleteChecked($ids)
    {
        try {
            $ret = [];
            foreach ($ids as $id) {
                $dbRes = self::query($id);
                if (!$dbRes[0]) {
                    $dbRes[1][3] = $id;
                    array_push($ret, $dbRes[1]);
                    continue;
                }
                //任务是否你是发布人
                $auth = Helper::auth([1]);
                if (!$auth[0]) {
                    if ($auth[1]['s_uid'] !== $dbRes[1]['founder']) {
                        array_push($ret, [ Errors::LIMITED_AUTHORITY[1][0], Errors::LIMITED_AUTHORITY[1][1] ,$id]);
                        continue;
                    }
                }
                $res = Db::table('b_task')->where('id', $id)->delete();
                array_push($ret, $res === 1 ?  ['id' => $id, 'res' => 'delete success'] : [ Errors::DELETE_ERROR[1][0], Errors::DELETE_ERROR[1][1] , $id]);
            }
            return [true , $ret];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function taskOverview($data, $uid){
        try {
            $query = Db::table("b_task")->alias('t');
            if (Helper::lsWhere($data, 'type')) $query->where('t.type', $data['type']);
            if (Helper::lsWhere($data, 'position_type')) $query->where('t.position_type', $data['position_type']);
            if (Helper::lsWhere($data, 'create_time_min')) $query->where('t.create_time', '>=', $data['create_time_min']);
            if (Helper::lsWhere($data, 'create_time_max')) $query->where('t.create_time', '<=', $data['create_time_max']);
            if (Helper::lsWhere($data, 'founder_name')){
                $query->view('u_user u3','name',"uid = t.founder");
                $query->whereLike('u3.name','%'.$data['founder_name'].'%');
            }
            //判断是否是管理员
            $auth = Helper::auth([1]);
            if (!is_array($auth)) $query->join('b_task_assigner ta', "t.id = ta.tid")->where('ta.uid', $uid);
            $query->join('u_user u', 'u.uid = t.recevier', 'left');
            $query->join('u_user u1', 'u1.uid = t.founder', 'left');
            if($data['app'] == 1){
                $a ='t.id, t.type, t.status, u1.name founder_name, t.create_time, t.positions';
                $b = '20';
            }else{
                $a = 't.id, t.name, t.type, t.status, u1.name founder_name, u.name recevier_name, t.create_time, t.deadline, t.positions';
                $b = '100';
            }
            $query->field($a);
            $query->order('t.update_time', 'desc');
            $dataRes = $query->limit($b)->select();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function listPort(){
        try{
            $dataRes['type'] =  Db::table('b_task')->field('type')->group('type')->select();
            $dataRes['position_type']=  Db::table('b_task')->field('position_type')->group('position_type')->select();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        }catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

}