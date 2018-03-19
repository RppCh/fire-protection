<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 0005
 * Time: 14:24
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\QuarantineDb;
use app\improve\validate\BaseValidate;


class QuarantineController extends Controller
{

    public function add ()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Quarantine.add');
        $data['adder'] = $auth[1]['s_uid'];
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = QuarantineDb::add($data);
        return Helper::reJson($dbRes);
    }

    public function ls ()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            'region' => 'max:20|region',
            'organization' => 'max:20'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = QuarantineDb::ls($data);
        return Helper::reJson($result);
    }

    public function query ()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Quarantine.query');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = QuarantineDb::query($data['id']);
        return Helper::reJson($dbRes);
    }

    public function edit()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Quarantine.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = QuarantineDb::edit($data);
        return Helper::reJson($dbRes);
    }

    function delete()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Quarantine.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = QuarantineDb::delete( $data['ids'] );
        return Helper::reJson($dbRes);
    }

}