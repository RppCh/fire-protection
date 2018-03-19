<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use Exception;
use think\Db;
use think\Error;

/**
 * 用户数据库操作
 * Created by xwpeng.
 */
class UserDb
{

    static function add($data)
    {
        try {
            Db::startTrans();
            if (!empty(self::queryByAccount($data['account']))) return [false, ['账号已存在']];
            $data['create_time'] = date('Y-m-d H:i:s', time());
            $data['update_time'] = $data['create_time'];
            $rids = $data['rids'];
            unset($data['rids']);
            Db::table('u_user')->insertGetId($data);
            foreach ($rids as $rid) {
                Db::name('u_user_role')->insert(["uid" => $data['uid'], "rid" => $rid]);
            }
            Db::commit();
            return [true , $data['uid']];
        } catch (Exception  $e) {
            try {
                Db::rollback();
            } catch (Exception $e) {
                return Errors::Error($e->getMessage());
            }
            return Errors::Error($e->getMessage());
        }
    }

    static function queryByAccount($account)
    {
        return Db::table('u_user')->where('account', $account)->find();
    }

    static function updateStatus($uid, $status)
    {
        try {
            if ($uid === '9adf8e29ec35844515c5a43938577ac8') return [false ,['系统管理员不能更新状态']];
            $db = Db::table('u_user')->where(["uid" => $uid])->update(['status' => $status]);
            return $db == 1 ?  [true ,$db] : Errors::UPDATE_ERROR;
        } catch (\think\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function edit($data)
    {
        try {
            Db::startTrans();
            if ($data['uid'] !== '9adf8e29ec35844515c5a43938577ac8') {
                //delete
                Db::table('u_user_role')->where("uid", $data['uid'])->delete();
                //insert
                if (isset($data['rids'])) {
                    foreach ($data['rids'] as $rid) {
                        Db::name('u_user_role')->insert(["uid" => $data['uid'], "rid" => $rid]);
                    }
                }
            }
            if ($data['uid'] === '9adf8e29ec35844515c5a43938577ac8') $data['status'] = 0;
            //update
            unset($data['rids']);
            $data['update_time'] = date('Y-m-d H:i:s', time());
            $dbRes = Db::table('u_user')->update($data);
            if ($dbRes > 0) {
                Db::commit();
                return [true , 1];
            }
            return [false ,["更新失败,uid找不到"]];
        } catch (\think\Exception $e) {
            try {
                Db::rollback();
            } catch (\think\Exception $e) {
                return Errors::Error($e->getMessage());
            }
            return Errors::Error($e->getMessage());
        }
    }

    static function query($uid)
    {
        try {
            $user = Db::table("u_user")->alias('nb')
                ->where("nb.uid", $uid)
                ->join('c_region q', 'q.id = nb.region')
                ->join('c_region q2', 'q2.id = q.parentId')
                ->column('nb.uid,nb.account,nb.region,nb.name,nb.dept,nb.status,nb.tel,q.name r1,q2.name r2');
            if (empty($user)) return [false ,['找不到该用户']];
            else $user = array_values($user)[0];
            $roles = Db::table("u_user_role")->alias('ur')
                ->where('ur.uid', $uid)
                ->join('u_role r', 'r.rid = ur.rid')
                ->field('r.rid,r.name')
                ->select();
            $user["roles"] = $roles;
            return [true ,$user];
        } catch (\think\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


    static function ls($data, $sample = false)
    {
        try {
            $query = DB::table("u_user")->alias('u');
            if (Helper::lsWhere($data, 'name')) $query = $query->whereLike("u.name", '%' . $data['name'] . '%');
//            if (Helper::lsWhere($data, 'dept')) $query = $query->where("u.dept", $data['dept']);
            $query->join('c_region r', ' r.id = u.region', 'left');
            $query->join('c_region r2', "r.parentId = r2.id", 'left');
//            $query->join('c_region r3', "r2.parentId = r3.id", 'left');
            if ($sample) {
                $query->field('u.uid,u.name');
                $query->where('u.status', 0);
            } else $query->field('u.uid,u.account,u.region,u.status,u.dept,u.name,r.name r1,r2.name r2,u.tel');
            $query->order('u.update_time', 'desc');
            $res = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return [true, $res];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryVerify($account)
    {
        try {
            return Db::table("u_verify")
                ->where("account", $account)
                ->find();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function login($account)
    {
        try {
            $user = Db::table("u_user")
                ->where("account", $account)
                ->column('uid,account,pwd,salt,region,name,status');
            if (empty($user)) return Errors::LOGIN_ERROR;
            else $user = array_values($user)[0];
            if ( $user['account'] !== $account ) return Errors::LOGIN_ERROR;
            $roles = Db::table("u_user_role")->alias('ur')
                ->where('ur.uid', $user['uid'])
                ->join('u_role r', 'r.rid = ur.rid')
                ->field('r.rid,r.name')
                ->select();
            $user["roles"] = $roles;
//            $pids = Db::table("u_user_role")->alias('ur')
//                ->where("ur.uid", $user['uid'])
//                ->join('u_role_premission rp', 'rp.rid = ur.rid')
//                ->field('rp.pid')
//                ->select();
//            $user['pids'] = $pids;
            return [true, $user];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function resetAuth($data)
    {
        try {
            $auth = Db::table('u_auth')->where('uid', $data['uid'])
                ->where('client', $data['client'])->column('uid');
            if (empty($auth)){
                $data = Db::table('u_auth')->insert($data);
                return $data == 1 ? [true , $data] : Errors::DATA_NOT_FIND;
            }
            $data = Db::table('u_auth')->update($data);
            return $data !== 0 ? [true , $data] : Errors::DATA_NOT_FIND;
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function deleteAuth($uid, $client = null)
    {
        try {
            $query = Db::table('u_auth')->where('uid', $uid);
            if (!empty($client)) $query = $query->where('client', $client);
            $dbRes = $query->delete();
            return $dbRes == 1 ? [true ,$dbRes] : Errors::AUT_LOGIN;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryAuth($uid, $s_token)
    {
        try {
            $res = Db::table('u_auth')->where('uid', $uid)
                ->where('s_token', $s_token)->column('s_update_time');
            return [true, $res];
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryPids($uid)
    {
        try {
            $pids = Db::table("u_user_role")->alias('ur')
                ->where("ur.uid", $uid)
                ->join('u_role_premission rp', 'rp.rid = ur.rid')
                ->field('rp.pid')
                ->select();
            return is_array($pids) && !empty($pids) ? [true  ,$pids] : [false, null] ;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryDepts()
    {
        try {
            $db = Db::table("u_dept")->select();
            return is_array($db) ? [true ,$db] :Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryRegionUser($parentId)
    {
        try {
            $region = Db::table('c_region')->where("parentId", $parentId)->field('id, name')->select();
            if (empty($region)) return Errors::DATA_NOT_FIND;
            $res['region'] = $region;
            $users = Db::table('u_user')->where('region', $parentId . '')->where('status', 0)->field('uid,name')->select();
            $res['user'] = $users;
            return [true ,$res];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

}