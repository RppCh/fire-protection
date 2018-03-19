<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

class CountryRecord extends BaseValidate
{

    protected $rule = [
        'id' => 'require|number',
        'region' => 'require|max:20|region',
        'hazard_type' => 'require|in:1,2,3,4',
        'pest_id' => 'require|number',
        'generation' => 'require|min:1|max:8',
        'happen_tense' => 'require|number|min:0|max:13',
        'plant_id' => 'require|number',
        'hazard_level' => 'require|in:1,2,3,4',
        'plant_cover_degree' => 'max:16',
        'pests_density' => 'require|max:16',
        'dead_tree_num' => 'number|min:0',
        'is_main_pests' => 'number|in:-1,1',
        'happen_level' => 'require|number|in:2,3,4',
        'distribution_area' => 'require|number',
        'damaged_area' => 'number',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'ids' => 'require|array',
        'del_images' => 'array|max:6',
    ];

    protected $scene = [
        'add' => [
            'region',
            'hazard_type',
            'pest_id',
            'generation',
            'happen_tense',
            'plant_id',
            'hazard_level',
            'plant_cover_degree',
            'pests_density',
            'dead_tree_num',
            'is_main_pests',
            'happen_level',
            'distribution_area',
            'damaged_area',
            'surveyer',
            'survey_time',
            'positions',
            'position_type',
        ],
        'id'=>['id'],
        'ids' => ['ids'],
        'edit' => [
            'id',
            'region',
            'hazard_type',
            'pest_id',
            'generation',
            'happen_tense',
            'plant_id',
            'hazard_level',
            'plant_cover_degree',
            'pests_density',
            'dead_tree_num',
            'is_main_pests',
            'happen_level',
            'distribution_area',
            'damaged_area',
            'positions',
            'position_type',
            'del_images',
        ],
    ];

}