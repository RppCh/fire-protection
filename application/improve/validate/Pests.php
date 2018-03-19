<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

use think\Validate;


class Pests extends Validate
{
    protected $rule = [
        'name' => 'require|max:16',
        'is_localed' => 'require|in:-1,1',
        'ids' => 'require|array',
        'id' => 'require|number',
        'danger_attributes' =>'max:16',
        'introduce' => 'max:255',
        'attach' => 'require|in:-1,1',
    ];

    protected $scene = [
        'local'=>['ids'],
        'id'=>['id'],
        'imageId'=>['imageId'],
        'edit'=>['id','danger_attributes','introduce','attach'],
    ];
}