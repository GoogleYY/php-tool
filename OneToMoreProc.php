class OneToMoreProc{
    public $appPath = '/home/app';
    public $runtimePath = 'template';
    public $procIndex = 'allNeedExecDIds';
    public $procArgName = 'processNo';
    public $procMainTimeout = 10800;
    public $procChileTimeout = 7200;
    public function __construct(){
        
    }
    public function initMoreProc($currDate, $start, $end, $allNeedExecDIds, $getActivityDriver, $aid, $rids, $processNo = NULL) {
        set_time_limit(3600*3);
        $filenameGetActivityDriver = APPPATH . "template/.getActivityDriver{$aid}.cache";
        $filenameAllNeedExecDIds = APPPATH . "template/.allNeedExecDIds{$aid}_xxx.cache";
        $limitProc = 10;    //最大进程数
        $processDrivers = 12000;    //每个进程处理的司机数量
        $processSignKey = getRedisPrefix(M_CT_PROCESS_SIGN_SET_ACTIVITY_DRIVER_CASE).$aid.'_';
        $processSignTime = 60*120;
        $filesCache = array();
        $this->load->library('RedisDB');
        //$processNo有可能是0
        if ($processNo !== NULL) {
            $filenameAllNeedExecDIds = str_replace('xxx', $processNo, $filenameAllNeedExecDIds);
            $allNeedExecDIds = explode(',', file_get_contents($filenameAllNeedExecDIds));
            $getActivityDriver = explode(',', file_get_contents($filenameGetActivityDriver));
            $filesCache[] = $filenameAllNeedExecDIds;
            $this->redisdb->set($processSignKey.$processNo , 1, $processSignTime);
        }
        if ($processNo === NULL && count($allNeedExecDIds) > $processDrivers) {
            $ridstr = implode('-', $rids);
            file_put_contents($filenameGetActivityDriver, implode(',', $getActivityDriver));
            if (count($allNeedExecDIds)/$limitProc > $processDrivers) {
                $processDrivers = ceil(count($allNeedExecDIds)/$limitProc);
            }
            foreach (array_chunk($allNeedExecDIds, $processDrivers, TRUE) as $k => $processDids) {
                $ifilename = str_replace('xxx', $k, $filenameAllNeedExecDIds);
                file_put_contents($ifilename, implode(',', $processDids));
                $filesCache[] = $ifilename;
                //开一个新的进程处理
                $this->openChildProcessSetCase($aid, $ridstr, $currDate, $start, $end, $k);
            }
            sleep(100);
            $while = 2;
            //循环检测子进程是否处理完毕@tudo
            while ($while) {
                $finish = 1;
                foreach ($filesCache as $f) {
                    if (file_exists($f)) {
                        $finish = 0;
                        break;
                    }
                }
                for ($i = 0; $i <= $k; $i++) {
                    if ($this->redisdb->get($processSignKey.$i)) {
                        $finish = 0;
                        break;
                    }
                }
                if ($finish) {
                    $while--;
                    sleep(60);
                }
                sleep(30);
            }
            $filesCache[] = $filenameGetActivityDriver;
            //处理完后删除临时缓存文件
            foreach ($filesCache as $f) {
                if (file_exists($f)) {
                    unlink($f);
                }
            }
            return TRUE;
        }
        $hour = date('h');
        $didCount = count($allNeedExecDIds);
        $iNo = 0;
        foreach ($allNeedExecDIds as $did) {
            $iNo++;
            if ($processNo !== NULL && $hour != date('h')) {
                $this->redisdb->set($processSignKey.$processNo , 1, $processSignTime);
            }
        }
        foreach ($filesCache as $f) {
            if (file_exists($f)) {
                unlink($f);
            }
        }
        $processNo !== NULL && $this->redisdb->delete($processSignKey.$processNo);
    }
    /**
     * 开启统计任务的子进程
     */
    public function openChildProcessSetCase($aid, $ridstr, $currDate, $start, $end, $processNo = NULL) {
        $cmd = "/home/xxx/php/bin/php /home/xxx/webroot/xxx/index.php commands/activityDriverReward/childProcessSetCase '$aid' '$ridstr' '$currDate' '$start' '$end' $processNo";
        $logFile = '/dev/null';
        $exec = $cmd . " > " . $logFile . " 2>&1 &";
        system('nohup ' . $exec);
    }
    /**
     * 统计任务的子进程入口
     */
    public function childProcessSetCase($aid, $ridstr, $currDate, $start, $end, $processNo = NULL) {
        if (!is_numeric($processNo)) {
            return FALSE;
        }
        $this->setDriverCase($currDate, $start, $end, array(), array(), $aid, explode('-', $ridstr), $processNo);
    }
}
