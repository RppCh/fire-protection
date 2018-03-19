<?php
/**
 * Created by 7Long.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 17:45
 */

namespace app\improve\validate;


class SamplePlotSurvey extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',
        'sample_plot_id' => 'require',
        'hazard_type' => 'require|in:1,2,3,4',
        'pest_id' => 'require|number',
        'generation' => 'require|min:1|max:8',
        'happen_tense' => 'require|number|min:0|max:13',
        'plant_id' => 'require|number',
        'hazard_level' => 'require|in:1,2,3,4',
        'plant_cover_degree' => 'max:16',
        'pests_density' => 'require|max:16',
        'dead_tree_num' => 'number|min:0',
        'happen_level' => 'require|number|in:2,3,4',
        'monitor_area' => 'require|number',
        'happen_area' => 'require|number',
        'distrib_area' => 'require|number',
        'ids' => 'require|array',
        'images' => 'array|max:6',
    ];

    protected $scene = [
        'add' => [
            'region',
            'hazard_type',
            'pest_id',
            'plant_id',
            'generation',
            'happen_tense',
            'happen_level',
            'hazard_level',
            'pests_density',
            'monitor_area',
            'happen_area',
            'distrib_area',
            'plant_cover_degree',
            'dead_tree_num',
            'images',
        ],
        'id'=>['id'],
        'imageId'=>['imageId'],
        'ids' => ['ids'],
        'edit' => [
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
            'happen_level',
            'monitor_area',
            'happen_area',
            'distrib_area',
            'images',
        ],
    ];
}