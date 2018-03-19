<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

use think\Validate;


class Region extends BaseValidate
{
    protected $rule = [
        'id' => 'require|max:20|region',
        'parentId' => 'require|max:20|region',
        'name' =>'require|max:30',
        'level' =>'require|number|max:1',
    ];

    protected $scene = [
        'query'  =>  ['parentId'],
    ];
}