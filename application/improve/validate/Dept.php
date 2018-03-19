<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

use think\Validate;


class Dept extends Validate
{
    protected $rule = [
        'parentId' => 'require|max:20',
    ];
}