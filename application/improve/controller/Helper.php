<?php

namespace app\improve\controller;

use app\improve\model\CommonDb;
use app\improve\model\UserDb;
use app\improve\model\BaseDb;
use think\Error;
use think\Exception;
use think\Validate;

/**
 * Created by PhpStorm.
 * User: xwpeng
 * Date: 2017/8/2
 * Time: 11:03
 */
class Helper
{


    private static function reJson2(array $res)
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Credentials:true');
        if (empty($res)) $res = Errors::Error('后端错误');
        return ['code' => $res[0] ? 's_ok' : 'error', 'var' => $res[1]];
    }

    static function reJson($res, $isTxt = false)
    {
        $res = self::reJson2($res);
        return $isTxt ? json_encode($res) : json($res);
    }

    static function getPostJson()

    {
        $data = file_get_contents('php://input');
        return json_decode($data, true);
    }

    public static function checkTel($tel)
    {
        if (empty($tel)) return 0;
        if (!preg_match_all('/^1[34578]\d{9}$/', $tel)) return 0;
        return 1;
    }

    public static function paramtersExists($data, array $params)
    {
        if (empty($data)) return 0;
        if (empty($params)) return 1;
        foreach ($params as $value) if (!array_key_exists($value, $data)) return 0;
        return 1;
    }

    static function paramtersNoEmpty($data, array $params)
    {
        if (empty($data)) return 0;
        if (empty($params)) return 1;
        foreach ($params as $value) if (empty($data[$value])) return 0;
        return 1;
    }

    /**
     * 仅仅支持获取post
     * @deprecated
     */
    static function getOkPostJson(array $params = [])
    {
        $data = self::getPostJson();
        if (empty($data)) $data = $_POST;
        if (self::paramtersExists($data, $params) && self::paramtersNoEmpty($data, $params)) return $data;
        return 0;
    }

    /**
     *支持get，post,json
     */
    static function getData(array $params = [])
    {
        $data = $_GET;
        if (!empty($data)) {
            if (self::paramtersExists($data, $params) && self::paramtersNoEmpty($data, $params)) return $data;
        }
        return self::getOkPostJson($params);
    }

    private static function checkPermission($permissionArr, $per)
    {
        $pass = false;
        foreach ($permissionArr as $p) {
            if (strpos($per, $p) !== false) {
                $pass = true;
                break;
            }
        }
        return $pass;
    }

    public static function checkSms($tel, $sms_code, $sms_id)
    {
        $record = CommonDb::querySms($tel, $sms_code, $sms_id);
        if (empty($record)) return 0;
        $sendTime = $record[0]['send_time'];
        $distance = 3 * 60 * 1000;
//         if ((Helper::getMillisecond() - $sendTime) > $distance) return 0;
        CommonDb::updateSmsStatus($sms_id, 1);
        return 1;
    }

    /**
     * 判断客户端类型，是Android or 微信 or web
     * 未开发iOS端，不考虑iPhone与iPad
     */
    static function getUserAgentType()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($userAgent, 'okhttp')) return "Android";
        if (strpos($userAgent, 'MicroMessenger')) return "wechat";
        return "web";
    }

    /**
     * 毫秒级时间戳
     */
    static function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

    /**
     * 密码过于简单判断
     */
    static function pwdEasy($pwd)
    {
        return (preg_match_all('/^[0-9]{1,}$/', $pwd)
            or preg_match_all('/^[a-z]{1,}$/', $pwd)
            or preg_match_all('/^[A-Z]{1,}$/', $pwd)
        );
    }

    /**
     * 唯一32位字符串
     */
    static function uniqStr()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * 随机唯一订单号，用于支付
     */
    static function orderNo()
    {
        $date = date("YmdHis");
        $arr = range(1000, 9999);
        shuffle($arr);
        return $date . $arr[0];
    }

    /**
     * 发送请求,返回请求结果
     */
    static function curl($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证服务器证书和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        if (!empty($data)) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($curl);
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
//            $header = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            return $body;
        }
        curl_close($curl);
        return $response;
    }

    /**
     * xml格式字符串转成数组
     */
    static function xmlToArray($xml)
    {
        if (empty($xml)) return '';
        libxml_disable_entity_loader(true);
        $xml_arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $xml_arr;
    }

    /**
     * 数组转成xml格式字符串
     */
    static function to_xml(array $params)
    {
        $xml = "<xml>";
        foreach ($params as $key => $val) {
//            if (!empty($val)) $xml .= "<" . $key . ">" . "<![CDATA[" . $val . "]]>" . "</" . $key . "> ";
            if (!empty($val)) $xml .= "<" . $key . ">" . $val . "</" . $key . "> ";
        }
        $xml .= "</xml>";
        return $xml;
    }

    static function unsetParams(array $arr, array $params)
    {
        if (empty($params) or empty($arr)) return $arr;
        foreach ($params as $p) {
            unset($arr[$p]);
        }
        return $arr;
    }

    static function decrypt($str, $key)
    {
        return openssl_decrypt(base64_decode($str), 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
    }

    static function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) $str .= $strPol[rand(0, $max)];
        return $str;
    }

    static function auth(array $pids = null)
    {
        $data = request()->cookie(['s_uid', 's_token']);
        if (!array_key_exists('s_uid', $data)) $data = request()->get(['s_uid', 's_token']);
        $msg = [
            's_uid.require' => "auth uid require",
            's_uid.length' => "auth uid length 32",
            's_token.require' => "auth token require",
            's_token.length' => "auth token length 32",
        ];
        $validate = new Validate([
            's_uid' => 'require|length:32',
            's_token' => 'require|length:32',
        ], $msg);
        if (!$validate->check($data)) return Errors::Error($validate->getError(), "身份认证失败,请重新登录");
        $auth = UserDb::queryAuth($data['s_uid'], $data['s_token']);
        if ($auth[0] === true) {
                $auth = $auth[1];
            if (empty($auth)) return Errors::AUTH_FAILED;
            $distance = time() - strtotime($auth[0]);
            if ($distance > 6 * 60 * 60) return Errors::AUTH_EXPIRED;
            //查找权限返回,根据uid查权限
            if (empty($pids)) return [true , $data];
            $dbPids = UserDb::queryPids($data['s_uid']);
            if (!$dbPids[0]) return Errors::AUTH_PREMISSION_EMPTY;
            $count = count($pids);
            foreach ($dbPids[1] as $arr) {
                if (in_array($arr['pid'], $pids)) $count--;
            }
            return $count === 0 ? [true , $data] : Errors::AUTH_PREMISSION_REJECTED;
        } else
            return $auth;
    }


    static function deleteFile($path)
    {
        //删除原文件
        try {
            unlink(Errors::FILE_ROOT_PATH . DS . $path);
        } catch (Exception $e) {
            return $e->getMessage();
            //写错误日志
        }
    }

    static function lsWhere($data, $key)
    {
        return array_key_exists($key, $data) && (!empty($data[$key] or $data[$key] === 0 or $data[$key] === '0'));
    }

    static function queryAdder($id, $db_name)
    {
        return $dbRes = BaseDb::queryAdder($id, $db_name);
    }

    // 查添加人是不是自己或者自己是管理员
    static function checkAdderOrManage($adder, $suid)
    {
        return (self::auth([1])[0] || $suid == $adder) ? [true] :Errors::LIMITED_AUTHORITY;
    }

    // 图片校验
    static function checkImage($imageCount, $image)
    {
        if ($imageCount > 5) return Errors::IMAGE_COUNT_ERROR;
        if (empty($image)) return Errors::IMAGE_NOT_FIND;
        if (!$image->checkImg()) return Errors::FILE_TYPE_ERROR;
        if (!$image->checkSize(2 * 1024 * 1024)) return Errors::IMAGE_FILE_SIZE_ERROR;
        return [true ];
    }

    static function sizecount($filesize)
    {
        if ($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' gb';
        } elseif ($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' mb';
        } elseif ($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' kb';
        } else {
            $filesize = $filesize . ' bytes';
        }
        return $filesize;
    }
}

?>