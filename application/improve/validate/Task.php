<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

class Task extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:32',
        'type' => 'require|in:1,2',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'deadline' =>'require|dateFormat:Y-m-d H:i:s',
        'content' =>'require|max:255',
        'assigner' => 'require|array',
        'order' => 'require|number|min:1|max:12',
        'id' =>'require|number',
        'ids' => 'require|array',
        'image_id' =>'require|number',
        'image_use' =>'require|in:1,2',
        'images' => 'array|max:6',
    ];

    protected $scene = [
        'add'  =>  ['name','type','positions','position_type','deadline','content','assigner','images'],
        'id'=>['id'],
        'ids'=>['ids'],
        'edit'=>['id','danger_attributes','harm_part','introduce','attach','images'],
        'deleteImage' => ['id', 'image_use','image_id'],
    ];
}