<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'IOneToMoreProc.php';

class OneToMoreProc extends CI_Controller {

    public $procSignTimeout = 10800;    //进程的超时时间
    protected $procArgIndex = 4;    //mainfuncArgs中第几个(从0开始计算)参数需要分割
    protected $processOneRow = 10000;   //每个进程处理的数量
    protected $processLimitCnt = 10;    //最多拆分成几个进程
    public $mainfuncArgs = array();     //传入mainfunc的参数数组
    public $id;
    public $processNo = null;
    public $callback = null;
    public $filesCache = array();
    public $needMoreProc = false;

    public function __construct($conf = array()) {
        parent::__construct();
        foreach ($conf as $k => $v) {
            $this->$k = $v;
        }
        if ($this->processNo === NULL && count($this->mainfuncArgs[$this->procArgIndex]) > $this->processOneRow) {
            $this->id = md5('id' . microtime(TRUE) . rand(0, 9999));
//            $filenameConfArgs = APPPATH . "/template/.Conf{$this->id}.cache";
//            $cacheConf = $conf;
//            if (isset($cacheConf['mainfuncArgs'])) {
//                unset($cacheConf['mainfuncArgs']);
//            }
//            file_put_contents($filenameConfArgs, serialize($cacheConf));
            $this->needMoreProc = TRUE;
        }
    }

    public function _log($param) {
        file_put_contents('/home/log/a.log', $param . "\r\n", FILE_APPEND);
    }

    public function construct() {
        set_time_limit($this->procSignTimeout);
        $filenameCommArgs = APPPATH . "/template/.CommArgs_xxx.cache";
        $filenamePrivateArgs = APPPATH . "/template/.PrivateArgs_xxx_yyy.cache";
        $limitProc = $this->processLimitCnt;    //最大进程数
        $processOneRow = $this->processOneRow;    //每个进程处理的数量
//        $processSignTime = $this->procSignTimeout;
        $this->filesCache = array();
//        $this->load->library('RedisDB');
//        $processSignKey = getRedisPrefix(M_CT_PROCESS_SIGN_SET_ACTIVITY_DRIVER_CASE) . $this->id . '_';
        if ($this->needMoreProc) {
            if (count($this->mainfuncArgs[$this->procArgIndex]) / $limitProc > $processOneRow) {
                $processOneRow = ceil(count($this->mainfuncArgs[$this->procArgIndex]) / $limitProc);
            }
            $commArgs = $this->mainfuncArgs;
            $commArgs[$this->procArgIndex] = NULL;
            file_put_contents(str_replace('xxx', $this->id, $filenameCommArgs), serialize($commArgs));
            foreach (array_chunk($this->mainfuncArgs[$this->procArgIndex], $processOneRow, TRUE) as $k => $processDids) {
                $file = str_replace(array('xxx', 'yyy'), array($this->id, $k), $filenamePrivateArgs);
                file_put_contents($file, serialize($processDids));
                $this->filesCache[] = $file;
                //开一个新的进程处理
                $this->openChildProcessById($this->id, $k);
            }
            sleep(30);
            $whileInit = 2;
            $while = $whileInit;
            //循环检测子进程是否处理完毕
            while ($while) {
                $finish = 1;
                foreach ($this->filesCache as $f) {
                    if (file_exists($f)) {
                        $finish = 0;
                        break;
                    }
                }
                if ($finish) {
                    $while--;
                    sleep(1);
                } else {
                    if ($while != $whileInit) {
                        $while = $whileInit;
                    }
                }
                sleep(1);
            }
//            $filenameConfArgs = APPPATH . "/template/.Conf{$this->id}.cache";
            $this->filesCache[] = str_replace('xxx', $this->id, $filenameCommArgs);
//            $filesCache[] = str_replace('xxx', $this->id, $filenameConfArgs);
            //处理完后删除临时缓存文件
            foreach ($this->filesCache as $f) {
                if (file_exists($f)) {
                    unlink($f);
                }
            }
            return TRUE;
        }
        //$processNo有可能是0
        if ($this->processNo !== NULL) {
            $this->mainfuncArgs = unserialize(file_get_contents(str_replace('xxx', $this->id, $filenameCommArgs)));
            $file = str_replace(array('xxx', 'yyy'), array($this->id, $this->processNo), $filenamePrivateArgs);
            $this->mainfuncArgs[$this->procArgIndex] = unserialize(file_get_contents($file));
            $this->filesCache[] = $file;
            $this->mainfuncArgs[0] = $this->processNo;
//            $this->redisdb->set($processSignKey . $this->processNo, 1, $processSignTime);
        }

        $this->mainfunc($this->mainfuncArgs);

        foreach ($this->filesCache as $f) {
            if (file_exists($f)) {
                unlink($f);
            }
        }
    }

    /**
     * 开启子进程
     */
    public function openChildProcessById($id, $processNo = NULL) {
        $class = get_class($this);
        $controller = lcfirst($class);
        $this->load->config('config_image', true);
        $image_config = $this->config->item('static_source_config', 'config_image');
        $phpPath = $image_config['otmp_child_proc_php_bin_path'];
        $indexPath = $image_config['otmp_child_proc_php_command_index'];
        $cmd = "$phpPath $indexPath commands/$controller/childProcessById '$id' $processNo";
        $logFile = '/dev/null';
        $exec = $cmd . " > " . $logFile . " 2>&1 &";
        system('nohup ' . $exec);
    }

    /**
     * 子进程入口
     */
    public function childProcessById($id, $processNo = NULL) {
        if (!is_numeric($processNo)) {
            return FALSE;
        }
        $filenameCommArgs = APPPATH . "/template/.CommArgs_xxx.cache";
        $params = unserialize(file_get_contents(str_replace('xxx', $id, $filenameCommArgs)));
        $conf = array();
        $conf['mainfuncArgs'] = $params;
        $conf['id'] = $id;
        $conf['processNo'] = $processNo;
        (new static($conf))->construct();
    }

//    public function mainfunc($params) {
//        
//    }
}
