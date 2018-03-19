<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 11:32
 */
namespace app\improve\controller;
use think\Controller;
use app\improve\model\RegularlyDb;
use app\improve\validate\BaseValidate;

class RegularlyController extends Controller{
    public function add(){
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Regularly.add');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $result = RegularlyDb::add($data);
        return Helper::reJson($result);
    }

    function ls()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            'region' => 'max:20|region',
            'pests' => 'number',
            'plant' => 'number',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = RegularlyDb::ls($data);
        return Helper::reJson($result);
    }

    function query()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Regularly.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = RegularlyDb::query($data['id']);
        return Helper::reJson($dbRes);
    }

    function edit()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Regularly.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = RegularlyDb::edit($data);
        return Helper::reJson($dbRes);
    }

    function deleteChecked()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Regularly.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = RegularlyDb::delete($data['ids']);
        return Helper::reJson($dbRes);
    }

    function cartogram(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'region' => 'require|region',
            'pests' => 'require|number',
            'year' => 'require|array',
        ]);
        if (!$validate->check($data)) return Helper::reErrorJson($validate->getError());
        $result = RegularlyDb::cartogram($data);
        return Helper::reJson($result);
    }

    function pestList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $result = RegularlyDb::pestList();
        return Helper::reJson($result);
    }

}