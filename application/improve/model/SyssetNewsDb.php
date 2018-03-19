<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/14 0014
 * Time: 11:35
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\UploadHelper;
use app\improve\controller\Helper;
use think\Exception;
use think\Db;
/*
 * 系统设置新闻系统model层
 */
class SyssetNewsDb
{
    // 添加
    static function add($data)
    {
        try {
            unset($data['id']);
            $attach = request()->file('attach');
            if (empty($attach)) return "not file";
            if (!$attach->checkSize(100 * 1024 * 1024)) return "max fileSize 100M";
            // 附件上传
            $preName = DS . 'sysset_news' . DS . 'attach' . DS . $attach->getInfo()['name'];
            $uploadRes = UploadHelper::upload($attach, $preName);
            if (!is_array($uploadRes)) return Helper::reErrorJson($uploadRes);
            $data['path']=$uploadRes[0];
            $data['filesize']=$attach->getSize();
            $data['type']=$attach->getType();
            $data['update_time'] = date('Y-m-d H:i:s', time());
            $dbRes = Db::table('b_sysset_news')->insertGetId($data);
            if ($dbRes < 1) return "add error";
            return $dbRes;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 查询添加者
    static function queryAdder($id)
    {
        try {
            $dbRes = Db::table('b_sysset_news')->where('id', $id)->field('adder')->find();
            if (empty($dbRes)) return Errors::DATA_NOT_FIND;
            return $dbRes;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 根据id查询
    static function query($id)
    {
        try {
            $dbRes = Db::table('b_sysset_news')
                ->where('id', $id)
                ->select()[0];
            if (empty($dbRes)) return "no data";
            return $dbRes;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 分页查询
    static function ls($data)
    {
        try {
            $query = Db::table("b_sysset_news")->order('update_time','desc');
            if (Helper::lsWhere($data,'title')) $query ->where('title', $data['title']);
            $dataRes = $query->paginate($data['per_page'],false,['page'=>$data['current_page']])->toArray();
            if (empty($dataRes)) return Errors::DATA_NOT_FIND;
            return $dataRes;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 删除选中
    static function deleteChecked($ids)
    {
        try {
            $ret = [];
            foreach ($ids as $id)
            {
                $dbRes = self::queryAdder($id);
                if (!is_array($dbRes))
                {
                    array_push($ret, ['id' => $id, 'res' =>Errors::DATA_NOT_FIND]);
                    continue;
                }
                //查添加人是不是自己或者自己是管理员
                $auth = Helper::auth([1]);
                $isManage = is_array($auth);
                if (!$isManage) {
                    if ($auth[1]['s_uid'] !== $dbRes['adder']){
                        array_push($ret, ['id' => $id, 'res' =>'u are not a manager or not an adder']);
                        continue;
                    }
                }
                $res = Db::table('b_sysset_news')->where('id', $id)->delete();
                array_push($ret, $res === 1 ? ['id' => $id, 'res' => 'delete success'] : Errors::DELETE_ERROR);
            }
            return $ret;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 编辑
    static function edit($data)
    {
        try {
            unset($data['adder']);
            $attach = request()->file('attach');
            if (empty($attach)) return "not file";
            if (!$attach->checkSize(100 * 1024 * 1024)) return "max fileSize 100M";
            // 附件上传
            $preName = DS . 'sysset_news' . DS . 'attach' . DS . $attach->getInfo()['name'];
            $uploadRes = UploadHelper::upload($attach, $preName);
            if (!is_array($uploadRes)) return Helper::reErrorJson($uploadRes);
            $data['path']=$uploadRes[0];
            $data['filesize']=$attach->getSize();
            $data['type']=$attach->getType();
            $data['update_time'] = date('Y-m-d H:i:s', time());
            $dbRes = Db::table('b_sysset_news')->update($data);
            return $dbRes === 1 ? 1 : 'update error';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}