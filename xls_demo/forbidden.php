<?php
require_once 'phpexcel/PHPExcel.php';
$dir = dirname(__FILE__);
$dp = $dir.'/forbidden.xls';
$xls = PHPExcel_IOFactory::load($dp);
$d = $xls->getSheet(0)->toArray();
$res = [];
foreach ($d as $i => $item) {
	if ($i == 0) {
		continue;
	}
	if (!$item[0]) {
		continue;
	}
	$data = [
		'driver_id' => $item[0],
		'type' => $item[1],
		'remark' => $item[2],
		'method' => $item[3],
		'startTime' => $item[4] ? $item[4] : date('Y-m-d H:i:s'),
		'day' => $item[5],
		'finance' => $item[6],
	];
	$res[] = $data;
}
$n = $dir.'/forbidden'.time().'.json';
file_put_contents($n, json_encode($res));
$a = file_get_contents($n);
print_r(json_decode($a, true));