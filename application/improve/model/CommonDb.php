<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
use think\Exception;

/**
 * 公共接口数据库操作
 * Created by xwpeng.
 */
class CommonDb
{
    static  function addRegion($data){
         try{
            return Db::table('c_region')->insert($data);
         }catch (Exception $e){
             return $e->getMessage();
         }
    }

    static  function queryRegion($parentId){
        try{
            return Db::table('c_region')->where("parentId", $parentId)->field('id value, name lable')->select();
        }catch (Exception $e){
            return $e->getMessage();
        }
    }

    static function getMaxVersion(){
        try{
            return Db::table('v_version')->column('max(version_code)');
        }catch (Exception $e){
            return $e->getMessage();
        }
    }

    static function getVersionInfo($versionCode){
        try{
            $dbRes = Db::table('v_version')
                ->where('version_code', $versionCode)->field('version_code, version_num, down_url, content, force')->find();
            return empty($dbRes) ? Errors::DATA_NOT_FIND : $dbRes;
        }catch (Exception $e){
            return $e->getMessage();
        }
    }

      static function addVersion($data){
        try{
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $dbRes = Db::table('v_version')->insertGetId($data);
            return $dbRes < 1 ? Errors::INSERT_ERROR : [$dbRes];
        }catch (Exception $e){
            return $e->getMessage();
        }
    }

    static function updateVersion($data){
        try{
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('v_version')->update($data);
            return $dbRes < 1 ? Errors::UPDATE_ERROR : [$dbRes];
        }catch (Exception $e){
            return $e->getMessage();
        }
    }

}