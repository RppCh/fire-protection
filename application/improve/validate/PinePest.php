<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/13
 * Time: 11:34
 */

namespace app\improve\validate;

use think\Validate;


class PinePest extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',
        'region' => 'require|max:20|region',
        'pinewood_area' => 'require|number|min:0',
        'survey_area' => 'require|number|min:0',
        'dead_pine_num' => 'require|number|min:0',
        'sampling_num' => 'number|min:0',
        'sampling_part_up' => 'number|min:0',
        'sampling_part_center' => 'number|min:0',
        'sampling_part_down' => 'number|min:0',
        'noline_pest' => 'number|min:0',
        'quasilinear_pest' => 'number|min:0',
        'otherline_pest' => 'number|min:0',
        'pineline_pest' => 'require|number|min:0',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'ids' => 'require|array',
        'images' => 'array|max:6',
        'delImages' => 'array|max:6',
    ];

    protected $scene = [
        'id'=>['id'],
        'ids'=>['ids'],
        'add' => [
            'pinewood_area', 'survey_area', 'dead_pine_num', 'sampling_num', 'sampling_part_up', 'sampling_part_center', 'sampling_part_down', 'noline_pest',
            'quasilinear_pest', 'pineline_pest', 'otherline_pest', 'region', 'positions', 'position_type', 'images',
        ],
        'edit' => [
            'pinewood_area', 'survey_area', 'dead_pine_num', 'sampling_num', 'sampling_part_up', 'sampling_part_center', 'sampling_part_down',
            'noline_pest', 'quasilinear_pest', 'pineline_pest', 'otherline_pest', 'region', 'images','delImages',
        ],
    ];

}