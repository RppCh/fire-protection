<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22 0022
 * Time: 15:22
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
use think\Error;
use think\Exception;

class BaseDb
{
    // 查询添加者
    static function queryAdder($id, $db_name)
    {
        try {
            $dbRes = Db::table($db_name)->where('id', $id)->field('adder')->find();
            return !empty($dbRes) ? [true ,$dbRes ] :Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 根据id删除
    static function deleteChecked($id,$db_name)
    {
        try {
            $dbRes = Db::table($db_name)->where('id', $id)->delete();
            return $dbRes === 1 ? [true] : 'delete error';
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}