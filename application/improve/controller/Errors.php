<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 10:52
 */

namespace app\improve\controller;

use app\improve\model\Error;

class Errors
{
    const PARAMS_ERROR = "params_error";
    const DB_ERROR = "db_error";
    const FILE_ROOT_PATH = ROOT_PATH . DS . 'public' . DS . 'file';
    const USER_ADD = "account already exists";
    const SAVE_FILE_ERROR = "save file error";
    const IS_NOT_I = "u are not task founder";
    const TASK_STATUS_ERROR_THREE = "task status is not 2";
    const VERSION_CODE_IS_NULL = "version_code is null";
    const NEW_VERSION_NOT_FIND = "new version not find";

    const ATTACH_NOT_FIND = [false, ["找不到附件", "attach not find"]];
    const AUT_LOGIN = [false, ["身份验证删除失败", "auth delete failed"]];
    const TASK_STATUS_ERROR_TWO = [false, ["任务正在执行", "task status is not 1"]];
    const NO_INCIDENT = [false, ["你不是任务接收人", "you are not receiver"]];
    const TASK_EXPIRED = [false, ["任务过期", "task expired"]];
    const TASK_STATUS_ERROR_ONE = [false, ["任务已被接收", "task status is not 0"]];
    const ASSIGN_ERROR = [false, ["你不是任务指派人", "u are not be assign"]];
    const DEADLINE_ERROR = [false, ["截止时间要大于目前时间", "deadline must > create_time"]];
    const UPDATE_ERROR = [false, ["修改错误", "update error"]];
    const FILE_SAVE_ERROR = [false, ["文件保存失败", "file_save_error"]];
    const NO_FILE = [false, ["没有传文件", "no file"]];
    const MAX_FILE_SIZE = [false, ["文件大小请不要超过100M", "max fileSize 100M"]];
    const NO_IMAGES_DELETED = [false, ["删除的图片没有找到", "no deleted img"]];
    const AUTH_FAILED = [false, ["身份认证失败,请重新登录", "auth not find in db"]];
    const AUTH_EXPIRED = [false, ["身份认证过期，请重新登录", "auth expired"]];
    const AUTH_PREMISSION_EMPTY = [false, ["没有权限", "premission empty"]];
    const AUTH_PREMISSION_REJECTED = [false, ["权限拒绝", "premission rejected"]];
    const DELETE_ERROR = [false, ["删除错误", "delete error"]];
    const LOGIN_ERROR = [false, ["您输入的登录名或密码错误", "account or pwd error"]];
    const DATA_NOT_FIND = [false, ["没有数据", "data not find"]];
    const LOGIN_STATUS = [false, ["用户已停用", "user status error"]];
    const ADD_ERROR = [false, ["添加错误", "add error"]];
    const IMAGE_COUNT_ERROR = [false, ["图片不能超过六张"]];
    const IMAGE_NOT_FIND = [false, ["没有图片", "image not find"]];
    const FILE_TYPE_ERROR = [false, ["文件格式错误"]];
    const IMAGE_FILE_SIZE_ERROR = [false, ["大小不能超2M"]];
    const IMAGES_INSERT_ERROR = [false, ["图片添加失败"]];
    const LIMITED_AUTHORITY = [false, ["你不是管理,也不是本人", "u are not a manager or not an adder"]];


    static function Error($toC, $toU = '程序出错', $isOk = false)
    {
        return [$isOk, [$toU, $toC]];
    }

    static function validateError($toC)
    {
        return [false, [$toC, $toC]];
    }
}

?>