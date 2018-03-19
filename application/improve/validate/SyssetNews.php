<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/14 0014
 * Time: 11:36
 */

namespace app\improve\validate;


use think\Validate;

// 系统设置新闻系统验证器
class SyssetNews extends Validate
{
    protected $rule = [
        'id' => 'require|number',
        'title' => 'require|max:32',
        'ids' => 'require|array',
    ];

    protected $scene = [
        'id'=>['id'],
        'ids' => ['ids'],
        'add' => [
            'title',
        ],
        'edit' => [
            'title',
        ],
    ];
}