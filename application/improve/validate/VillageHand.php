<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

class VillageHand extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',
        'region' => 'require|max:20|region',
        'hazard_type' => 'require|in:1,2,3,4',
        'happen_time' => 'require|dateFormat:Y-m-d H:i:s',
        'pest_id' => 'require|number',
        'hand_method' => 'require|number|in:1,2,3,4',
        'drug_amount' => 'min:0',
        'hand_cost' => 'number|min:0',
        'hand_area' => 'number',
        'happen_area' => 'number',
        'hand_effect' => 'max:16|per',
        'save_pest_area' => 'min:0',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'ids' => 'require|array',
        'drug_name' => 'require|max:16',
        'del_images' => 'array|max:6',
    ];

    protected $scene = [
        'id' => ['id'],
        'ids' => ['ids'],
        'add' => [
            'region', 'hazard_type', 'happen_time',
            'pest_id', 'hand_method', 'drug_amount',
            'hand_cost', 'hand_area', 'happen_area',
            'hand_effect', 'save_pest_area', 'positions',
            'position_type','drug_name',
            ],
        'edit' => [
            'region', 'hazard_type', 'happen_time',
            'pest_id', 'hand_method', 'drug_amount',
            'hand_cost', 'hand_area', 'happen_area',
            'hand_effect', 'save_pest_area', 'positions',
            'position_type','id','drug_name','del_images'
        ],
    ];

}