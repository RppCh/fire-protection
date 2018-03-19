<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 14:18
 */
namespace app\improve\validate;

class Regularly extends BaseValidate{
    protected $rule = [
        'id' => 'require|max:20',
        'region' => 'require|max:20|region',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'number' => 'require|number|max:8',
        'pests' => 'require|number',
        'plant' => 'require|number',
        'regularly_area' => 'require|number',
        'representative_area' => 'require|number',
        'ids' => 'require|array',
    ];

    protected $scene = [
        'add' => [
            'region',
            'positions',
            'position_type',
            'number',
            'plant',
            'pests',
            'regularly_area',
            'representative_area',
            ],
        'id' => [
            'id',
            ],
        'edit' => [
            'id',
            'region',
            'positions',
            'position_type',
            'number',
            'plant',
            'pests',
            'regularly_area',
            'representative_area',
        ],
         'ids' => [ 'ids'],
    ];
}