<?php
/**
 * 文件描述
 * Created by PhpStorm.
 * User: wendaomumao
 * Date: 2018/3/12 0012
 * Time: 10:46
 */

namespace app\improve\controller;

use app\improve\model\LocationDb;
use think\Controller;
use think\Cache;

class LocationController extends Controller
{
    public function location(){
        $auth = Helper::auth();
        if ($auth[0] !== true) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $data['uid'] = $auth[1]['s_uid'];
        $data['tel'] = LocationDb::IdQuery( $data['uid'] );
        $db = LocationDb::ls();
        if ( $db[0] ){
                $dbRes = [];
                foreach ( $db[1] as $key =>$value){
                    if ( Cache::has($value['uid']) ) {
                        $dbRes[$key] = Cache::get($value['uid']);
                    }
                    else {
                        LocationDb::delete( $value['uid'] );
                    }
                }
                Cache::set($data['uid'],$data,50);
                return Helper::reJson([true ,[$dbRes]]);
        }
        else{
            Cache::set($data['uid'],$data,50);
            return Helper::reJson([true ,[]]);
        }
//        if ( empty( $data )){
//            $data['uid'] = $auth[1]['s_uid'];
//            $data['tel'] = LocationDb::IdQuery( $data['uid'] );
//            $db = LocationDb::ls();
//            if ( $db[0] ){
//                $data = [];
//                foreach ( $db[1] as $key =>$value){
//                    if ( Cache::has($value['push_name']) ){
//                        $data[$key] = Cache::get($value['push_name']);
//                    }
//                    else{
//                        LocationDb::delete( $value['push_name'] );
//                    }
//                }
//                return Helper::reJson([true ,[$data]]);
//            }
//            die();
//        }
//        if ( LocationDb::IQuery( $uid )){
//            Cache::set($data['push_name'],$data,50);
//            die();
//        }
//        else{
//            LocationDb::add( $uid );
//            Cache::set($data['push_name'],$data,50);
//            die();
//        }
    }
}