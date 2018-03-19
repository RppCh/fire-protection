<?php
/**
 * 文件描述
 * Created by PhpStorm.
 * User: wendaomumao
 * Date: 2018/3/12 0012
 * Time: 12:47
 */

namespace app\improve\model;

use think\Db;

class LocationDb
{
    static function IdQuery( $data )
    {
        $result = Db::table('u_user')->where('uid' , $data )->find() ;
        return empty($result) ? false : $result['tel'];
    }

    static function query( $data )
    {
        $result = Db::table('b_location')->where('push_name' ,$data)->find() ;
        return empty($result) ? false : $result['tel'];
    }

    static function IQuery( $data )
    {
        $result = Db::table('b_location')->where('uid' ,$data)->find() ;
        return empty($result) ? true : false ;
    }

    static function ls()
    {
        $db = Db::table('b_location')->select();
        return empty( $db ) ?  [ false , null] : [ true ,$db ] ;
    }

    static function add( $uid )
    {
        $db = Db::table('b_location')->insert(['uid'=> $uid ]);
        return $db == 1 ?  ["接收成功"] : ["接收失败"];
    }

    static function delete( $data )
    {
         $db = Db::table('b_location')->where('push_name' ,$data)->delete();
         return $db == 1 ?  ["接收成功"] : ["接收失败"];
    }

    static function refresh( $data )
    {
        $db = Db::table('b_location')->where('push_name' , $data['push_name'] )->update($data);
        return $db === 1 ?  true : false ;
    }
}