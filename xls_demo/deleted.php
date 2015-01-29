<?php
require_once 'phpexcel/PHPExcel.php';
$dir = dirname(__FILE__);
$dp = $dir.'/deleted.xls';
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
	];
	$res[] = $data;
}
$n = $dir.'/deleted'.time().'.json';
file_put_contents($n, json_encode($res));
$a = file_get_contents($n);
print_r(json_decode($a, true));