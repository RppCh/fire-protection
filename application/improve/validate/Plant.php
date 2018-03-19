<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

use think\Validate;


class Plant extends Validate
{
    protected $rule = [
        'ids' => 'require|array',
        'id' => 'require|number',
        'order' => 'require|in:1,2,3,4,5,6',
        'introduce' => 'max:255',
        'name' => 'require|max:16',
        'start' => 'require|number|min:0',
        'end' => 'require|number|gt:start|end:start',
        'attach' => 'require|in:-1,1',
    ];

    protected $scene = [
        'local' => ['ids'],
        'query' => ['id'],
        'edit' => ['id', 'introduce','attach'],
        'ls' => ['start', 'end'],
        'id'=>['id'],
        'imageId'=>['imageId'],
    ];

}