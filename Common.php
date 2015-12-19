<?php
class Common {
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
