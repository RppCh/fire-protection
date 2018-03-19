<?php
/**
 * Created by xwpeng
 * Date: 2017/11/27
 */

namespace app\improve\controller;

use app\improve\model\CountryRecordDb;
use app\improve\model\RoleDb;
use think\Db;

class  TestController
{
    function first()
    {
        return "first ok ";
    }

    private  function addRegion(){
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Region');
        if (true !== $result) return Helper::reErrorJson($result);
//        var_dump($data);
        return CommonDb::addRegion($data);
    }

    private   function addRegionSource(){
        $source = "源水村委会[430528209201]  笑岩村委会[430528209202]  石塘村委会[430528209203]  靖位村委会[430528209204]  烟山村委会[430528209205]  潮水村委会[430528209206]  永兴村委会[430528209207]";
        $source = str_replace(" ", "", $source);
        $sp1 = explode("]", $source);
//        var_dump($sp1);
        $ret = [];
        $l = count($sp1) - 1;
        for ($i = 0; $i < $l; $i++) {
            $sp2 = explode("[",$sp1[$i]);
//            var_dump(count($sp2));
            array_push($ret,[
                'name'=>$sp2[0],
                'id'=>$sp2[1],
                'level'=>5,
                'parentId'=>'430528209'
            ]);
        }
//       var_dump($ret);
        foreach ($ret as $item) {
            echo Helper::curl("http://192.168.6.50/improve/common/addRegion", json_encode($item));
        }
    }

    //批量插入数据
    function bulkInsert()
    {
        $auth = Helper::auth();
        if (!is_array($auth)) return Helper::reErrorJson($auth);
        $data['adder'] = $auth['s_uid'];
        $result = CountryRecordDb::bulkInsert($data);
        if (is_string($result)) return Helper::reSokJson($result);
        return Helper::reErrorJson($result);
    }

    function addCountryRecord(){
        CountryRecordDb::bulkInsert();
    }

    function test(){
//        $a = Db::table('b_survey_record_image')->field('path')->whereIn('id',[4,5,6,7])->select();
       $a = Db::table('b_survey_record_image')->field('path')->where('country_record_id', 89)->whereIn('id',[8,16,17,18])->select();
        var_dump($a);
    }

}

?>