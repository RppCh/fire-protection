<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22 0022
 * Time: 11:53
 */
namespace app\improve\validate;
use think\Validate;
class LawFile extends Validate{
     protected $rule= [
         'sort|标题' => 'require|in:1,2',
         'title|类别' => 'require|max:10',
         'content|内容' => 'require|max:255',
         'adder|添加者' => 'require|max:32',
         'id|序列号' => 'require|number',
         'ids|序列号组' => 'require|array',
     ];
     protected $scene = [
         'add' => ['sort','title','content'],
         'edit' => ['id','sort','title','content','adder'],
         'id' => ['id'],
         'ids' => ['ids'],

     ];
}