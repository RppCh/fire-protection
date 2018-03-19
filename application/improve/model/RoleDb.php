<?php

namespace app\improve\model;

use app\improve\controller\Helper;
use Exception;
use think\Db;

/**
 *角色权限数据库操作
 * Created by xwpeng.
 */
class RoleDb
{
    /**
     * @name 角色名
     * @pids 权限数组
     * @return mixed 1：操作成功，没加权限。2：操作成功，加了权限。String:错误信息
     * todo:数据库连接错误可不可以再优化
     */
    static function add($data)
    {
        try {
            Db::startTrans();
            $rid = Db::table('u_role')->insertGetId(["name" => $data['name']]);
            if (!isset($data['pids'])) {
                Db::commit();
                return 1;
            }
            foreach ($data['pids'] as $pid) {
                Db::name('u_role_premission')->insert(["rid" => $rid, "pid" => $pid]);
            }
            Db::commit();
            return 2;
        } catch (Exception  $e) {
            try {
                Db::rollback();
            } catch (Exception $e) {
                return $e->getMessage();
            }
            return $e->getMessage();
        }
    }

    static function delete($rid)
    {
        try{
            if ($rid == 1) throw new \think\Exception('system admin role cannot be delete');
            return Db::table('u_role')->delete(["rid" => $rid]);
        } catch (\think\Exception $e) {
            return $e->getMessage();
        }

    }

    static function edit($data)
    {
        try {
            Db::startTrans();
            //delete
            if ($data['rid'] === 1) return 'system admin cannot be edit';
            Db::table('u_role_premission')->where("rid", $data['rid'])->delete();
            //insert
            if (isset($data['pids'])) {
                foreach ($data['pids'] as $pid) {
                    Db::name('u_role_premission')->insert(["rid" => $data['rid'], "pid" => $pid]);
                }
            }
            //update
            unset($data['pids']);
            $dbRes = Db::table('u_role')->update($data);
            if ($dbRes > 0) {
                Db::commit();
                return 1;
            }
            throw new \think\Exception("update fail,rid not find?");
        } catch (\think\Exception $e) {
            try {
                Db::rollback();
            } catch (\think\Exception $e) {
                return $e->getMessage();
            }
            return $e->getMessage();
        }
    }

    static function query($rid)
    {
        try {
            $role = Db::table("u_role")->alias('r')
                ->where("r.rid", $rid)
                ->select();
            if (empty($role)) throw new \think\Exception("role not find ");
            else $role = $role[0];
            $pids = Db::table("u_role_premission rp")->alias('rp')
                ->where('rp.rid', $rid)
                ->join('u_premission p', 'p.pid = rp.pid')
                ->field('p.pid,p.name')
                ->select();
            $role["permission"] = $pids;
            return $role;
        } catch (\think\Exception $e) {
            return $e->getMessage();
        }
    }

    static function ls($data)
    {
        try {
            $query = DB::table("u_role")->limit($data['start'], $data['end'] - $data['start']);
            return $query->select();
        } catch (\think\Exception $e) {
            return $e->getMessage();
        }
    }

    static function lsPermission()
    {
        try {
            return Db::table("u_premission")->select();
        } catch (\think\Exception $e) {
            return $e->getMessage();
        }
    }
}