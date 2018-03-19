<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

class User extends BaseValidate
{
    protected $rule = [
        'account' => 'require|length:3,16|alphaAndNumber',
        'pwd' => 'length:6,16|different:account',
        'pwds' => 'length:6,16|different:account|alphaDash',
        'region' => 'require|max:20|region',
        'name' => 'require|max:16',
        'status'=>"in:-1,0",
        'rids|角色'=>"require|array",
        'uid'=>"require|length:32",
        'client'=>'require|in:1,2',
        'tel'=>'require|tel'
    ];

    protected $message = [
        'account.require' => '登录名必填',
        'account.length' => '登录名长度需3到16',
        'pwd.length' => '密码长度需6到16',
        'pwds.length' => '密码长度需6到16',
        'pwd.different:account'     => '密码不能与账号相同',
        'pwds.different:account'     => '密码不能与账号相同',
        'pwds.alphaDash'   => '密码只能包含字母，下划线，数字',
        'region.require'  => '区域必填',
        'region.max'  => '区域最多20个字符',
        'name.require'        => '名字必填',
        'name.max'        => '名字最多16个字符',
        'tel.require'        => '手机号码必填',
    ];


    protected function tel($value)
    {
        return preg_match_all('/^1[34578]\d{9}$/', $value) ? true : '手机号码格式错误';
    }

    protected function alphaAndNumber($value)
    {
        $regex = '/^[a-zA-Z][a-zA-Z0-9]{2,15}$/';
        return preg_match($regex, $value) ? true : '登录名必须填写以字母开头的名称';
    }

    protected $scene = [
        'add'  =>  ['account','pwds','region','name','rids','tel'],
        'status'  =>  ['uid','status'],
        'edit'  =>  ['account','pwd','status','region','name','rids','uid','tel'],
        'query'  =>  ['uid'],
        'login'=>['account','pwd','client'],
        'loginOut'=>['client'],
    ];

}