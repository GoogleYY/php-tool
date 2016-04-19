<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'otmp' . DIRECTORY_SEPARATOR . 'OneToMoreProc.php';

class OtmpSetDriverCase extends OneToMoreProc implements IOneToMoreProc {
    protected $procArgIndex = 4;    //mainfuncArgs中第几个(从0开始计算)参数需要分割
    protected $processOneRow = 12000;   //每个进程处理的数量

    public function mainfunc($params) {
        $this->load->model('activity/ActivityRuleDriverCase');
        call_user_func_array(array($this->ActivityRuleDriverCase, 'setDriverCase'), $params);
    }

}
