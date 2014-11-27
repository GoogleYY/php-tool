<?php

require_once WEB_ROOT . DS . 'library' . DS . 'You' . DS . 'Utils' . DS . 'upyun.class.php';

class Model_Upyun extends UpYun {

    static public $_models;
    static public $bucketname;
    static public $username;
    static public $password;

    /**
     * Returns the static model of the specified AR class.
     * @param type $className
     * @return Model_Upyun the static model class
     */
    public static function model($endpoint = NULL, $timeout = 30) {
        $model = null;
        if (isset(self::$_models)) {
            $model = self::$_models;
        } else {
            $className = __CLASS__;
            $config = new Zend_Config_Ini(INI_FILE, APPLICATION_ENV);
            self::$bucketname = $config->upyun->bucketname;
            self::$username = $config->upyun->username;
            self::$password = $config->upyun->password;
            $model = self::$_models = new $className(self::$bucketname, self::$username, self::$password, $endpoint, $timeout);
        }
        return $model;
    }

    public function fresh($file) {
        $url = 'http://bxapk.b0.upaiyun.com' . $file;
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $bucketname = self::$bucketname;
        $password = md5(self::$password);
        $Authorization = "{$url}&{$bucketname}&{$date}&{$password}";
        $sign = md5($Authorization);
        $A = 'UpYun ' . self::$bucketname . ':' . self::$username . ':' . $sign;
        $body = ['purge' => $url];
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://purge.upyun.com/purge/");
            $_headers = array('Expect:');
            array_push($_headers, "Authorization: {$A}");
            array_push($_headers, "Date: {$date}");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $response = curl_exec($ch);
            $httpInfo = curl_getinfo($ch);
            $e = curl_error($ch);
            if ($e) {
                throw new Exception($e);
            }
            curl_close($ch);
        } catch (Exception $e) {
            $response = $e->getMessage();
        }
        if ($httpInfo['http_code'] / 100 == 2) {
            $response = json_decode($response, TRUE);
        }
        $res = [
            'code' => $httpInfo['http_code'],
            'data' => $response,
        ];
        return $res;
    }

}
