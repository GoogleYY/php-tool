<?php
class Common {
	



    /**
     * 定时任务开头标准输出
     * @param unknown_type $str
     * @return string
     */
    public static function jobBegin($str = '')
    {
        $ext_str = empty($str) ? '' : trim($str);
        return "\r\n------" . $ext_str . '------Job start: ' . date('Y-m-d H:i:s', time()) . "------------\r\n";
    }

    /**
     * 定时任务结尾标准输出
     * @param unknown_type $str
     * @return string
     */
    public static function jobEnd($str = '')
    {
        $ext_str = empty($str) ? '' : trim($str);
        return "\r\n------" . $ext_str . '------Job end: ' . date('Y-m-d H:i:s', time()) . "------------\r\n";
    }

    /**
     * 生成随机字符串方法
     * @return int $length
     * @return int $type
     */
    public static function genRandStr($length = 6, $type = 0)
    {
        $randstr = '';
        switch ($type) {
            case 0:
                $ascii_start = 97;
                $ascii_end = 122;
                break;
            case 1:
                $ascii_start = 65;
                $ascii_end = 90;
                break;
            case 2:
                $ascii_start = 48;
                $ascii_end = 57;
                break;
            default:
                $ascii_start = 97;
                $ascii_end = 122;
                break;
        }
        for ($i = 0; $i < $length; $i++) {
            $randstr .= chr(mt_rand($ascii_start, $ascii_end));
        }
        return $randstr;
    }

    /**
     * 验证身份证号
     * @param $id_card
     * @return bool
     */
    public static function checkIdCard($id_card) { // 检查是否是身份证号
        if (strlen($id_card) != 15 && strlen($id_card) != 18) {
            return false;
        }
        $id_card = self::parseIDCard($id_card);
        // 转化为大写，如出现x
        $number = strtoupper($id_card);
        //加权因子
        $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码串
        $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        //按顺序循环处理前17位
        $sigma = 0;
        for ($i = 0;$i < 17;$i++) {
            //提取前17位的其中一位，并将变量类型转为实数
            $b = (int) $id_card{$i};
            //提取相应的加权因子
            $w = $wi[$i];
            //把从身份证号码中提取的一位数字和加权因子相乘，并累加
            $sigma += $b * $w;
        }
        //计算序号
        $snumber = $sigma % 11;

        //按照序号从校验码串中提取相应的字符。
        $check_number = $ai[$snumber];
        if ($id_card{17} == $check_number) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据身份证号获得出生日期
     * @param $id_card
     * @return string
     */
    public static function getBirthDayByIdCard($id_card) {
        $id_card = self::parseIDCard($id_card);
        $birth_day = substr($id_card,6,8);
        return $birth_day;
    }

    /**
     * 将15位身份证号转成18位
     * @param $idCard
     * @return string
     */
    public static function parseIDCard($idCard) {
        // 若是15位，则转换成18位；否则直接返回ID
        if (15 == strlen ( $idCard )) {
            $W = array (7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,1 );
            $A = array ("1","0","X","9","8","7","6","5","4","3","2" );
            $s = 0;
            $idCard18 = substr ( $idCard, 0, 6 ) . "19" . substr ( $idCard, 6 );
            $idCard18_len = strlen ( $idCard18 );
            for($i = 0; $i < $idCard18_len; $i ++) {
                $s = $s + substr ( $idCard18, $i, 1 ) * $W [$i];
            }
            $idCard18 .= $A [$s % 11];
            return $idCard18;
        } else {
            return $idCard;
        }
    }
    
	/**
	 * 根据时间戳获取哪一年的第几周（年末年初有交叉的算是下一年的第一周）
	 * @param <int> $time
	 * @return <array>  array('Y'=>年份, 'W'=>第几周)
	 * @author liuxiaobo
	 * @since 2014-1-17
	 */
	public static function getWeekth($time){
		$week = date('W', $time);
		$month = date('m', $time);
		$year = date('Y', $time);
		if(1 == $week && 12 == $month){
			$year += 1;
		}
		return array(
			'Y'=>$year,
			'W'=>$week,
		);
	}
	
	/**
	 * 判断两个时间戳是不是同一周
	 * @param <int> $time1
	 * @param <int> $time2
	 * @return <bool>
	 * @author liuxiaobo
	 * @since 2014-1-17
	 */
	public function isSameWeek($time1, $time2){
		$week1 = Common::getWeekth($time1);
		$week2 = Common::getWeekth($time2);
		return ($week1['Y'] == $week2['Y'] && $week1['W'] == $week2['W']) ? TRUE : FALSE;
	}
	
	/**
	 * 用 mb_strimwidth 来截取字符，使中英尽量对齐。
	 * @author liuxiaobo
	 * @since 2014-1-17
	 */
	public static function wsubstr($str, $start, $width, $trimmarker = '...') {
		$_encoding = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
		return mb_strimwidth($str, $start, $width, $trimmarker, $_encoding);
	}
	
	/**
	 * 可逆加密，返回加密后的字符串
	 * @param <str> $data   加密前的字符串
	 * @param <str> $key    密钥（加密、解密 时应该是同一个密钥）
	 * @return <str>
	 * @link Common::decrypt($data, $key) 解密函数
	 * @author liuxiaobo
	 * @since 2014-2-27
	 */
	public static function encrypt($data, $key) {
		$key = md5($key);
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		$char = '';
		$str = '';
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) {
				$x = 0;
			}
			$char .= $key{$x};
			$x++;
		}
		for ($i = 0; $i < $len; $i++) {
			$str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
		}
		return base64_encode($str);
	}
	
	/**
	 * 可逆加密，返回解密后的字符串
	 * @param <str> $data   加密后的字符串
	 * @param <str> $key    密钥（加密、解密 时应该是同一个密钥）
	 * @return <str>
	 * @link Common::encrypt($data, $key) 加密函数
	 * @author liuxiaobo
	 * @since 2014-2-27
	 */
	public static function decrypt($data, $key) {
		$key = md5($key);
		$x = 0;
		$data = base64_decode($data);
		$len = strlen($data);
		$l = strlen($key);
		$char = '';
		$str = '';
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) {
				$x = 0;
			}
			$char .= substr($key, $x, 1);
			$x++;
		}
		for ($i = 0; $i < $len; $i++) {
			if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
				$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
			} else {
				$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
			}
		}
		return $str;
	}
}
