<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

use think\Validate;


class Role extends Validate
{
    protected $rule = [
        'name' => 'require|max:16',
        'pids' => 'array',
        'rid' =>'require|number',
        'start' =>'require|number|min:0',
        'end' =>'require|number|gt:start|end:start',
    ];

    protected $scene = [
        'add'  =>  ['name','pids'],
        'delete'  =>  ['rid'],
        'query'  =>  ['rid'],
        'ls'=>['start','end'],
        'edit'=>['name','pids','rid'],
    ];

    protected function end($value, $a ,$data)
    {
        return $value - $data[$a] <= 50 ? true : '最多查50条记录';
    }
}