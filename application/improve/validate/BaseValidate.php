<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 10:08
 */

namespace app\improve\validate;


use think\Validate;

class BaseValidate extends Validate
{

    // 自定义验证规则
    //并且需要注意的是，自定义的验证规则方法名不能和已有的规则冲突。

    protected function region($value)
    {
        return strpos($value, '430528') === 0 ? true : '区域必须在新宁县内';
    }

    protected function positionReg($value)
    {
//        $regex = '/^(\(-?((0|1?[0-7]?[0-9]?)(([.][0-9]{1,6})?)|180(([.][0],{1,6})?))\,-?((0|[1-8]?[0-9]?)(([.][0-9]{1,6})?)|90(([.][0]{1,6})?))\)\;)*$/';
        $regex = '/^(\(-?((0|1?[0-7]?[0-9]?)(([.][0-9]{1,15})?)|180(([.][0],{1,15})?))\,-?((0|[1-8]?[0-9]?)(([.][0-9]{1,15})?)|90(([.][0]{1,15})?))\)\;)*$/';
        return preg_match($regex, $value) ? true : 'position format must (x.x,y.y);(x.x,y.y);... ';
    }

    protected function per($value)
    {
        ///^(100|[1-9]?\d(\.\d\d?\d?)?)%$|0$/
        $reg = "/^(0|100|[1-9]?\d)%$/";
        return preg_match($reg, $value) ? true : 'hand_effect must in 0%-100%';
    }

    function end($s1, $end)
    {
        return substr($s1, -1, strlen($end)) === $end;
    }

    /* protected $msg  =   [
       'account.require' => 'account必须',
       'account.min'     => 'account最小3个字符',
       'account.max'     => 'account最大25个字符',
       'age.number'   => '年龄必须是数字',
       'age.between'  => '年龄只能在1-120之间',
       'email'        => '邮箱格式错误',
   ];*/

}