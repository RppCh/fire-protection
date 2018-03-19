<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 0005
 * Time: 14:29
 */

namespace app\improve\validate;

class Quarantine extends BaseValidate
{
    protected $rule = [
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'region' => 'require|max:20|region',
        'organization' => 'require|max:20',
        'found_time' => 'require|dateFormat:Y-m-d',
        'nature' => 'require|in:1,2',
        'tel'=>'require|tel',
        'administrator'=>'require|max:55',
        'id' => 'require|max:20',
        'adder' => 'require|length:32',
        'ids' => 'require|array'
    ];

    protected $scene = [
        'add' => ['positions','region','organization','found_time','nature','tel','administrator','position_type'],
        'query' => ['id'],
        'edit' => ['id', 'positions','region','organization','found_time','nature','tel','administrator','position_type'],
        'ids'=>['ids'],
    ];

    protected function tel($value)
    {
        return preg_match_all('/^1[34578]\d{9}$/', $value) ? true : '手机号码格式错误';
    }
}