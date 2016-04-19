<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ActivityDriverReward extends CI_Controller {

    public function test() {
        $allNeedExecDIds = array(
            111111111111111,
            111111111111112,
            111111111111113,
            111111111111114,
            111111111111115,
            111111111111116,
            111111111111117,
        );
        $getActivityDriver = array(
            
        );
        $this->moreProcSetDriverCase('2016-04-17', '2016-04-17', '2016-04-17', $allNeedExecDIds, $getActivityDriver, 111, array(1,2,3));
    }

    public function moreProcSetDriverCase() {
        $params = func_get_args();
        array_unshift($params, NULL);
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'otmpSetDriverCase.php';
        $conf = array('mainfuncArgs' => $params);
        (new OtmpSetDriverCase($conf))->construct();
    }
}
