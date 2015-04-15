<?php
ini_set('memory_limit', '2560M');
require_once 'phpexcel/PHPExcel.php';

class FileToXls {
	
	public static function cells() {
		$r = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T');
		return $r;
	}
	
	public static function to($files) {
		$cells = self::cells();
		$phpExcel = new PHPExcel();
		$n = 0;
		foreach ($files as $i => $file) {
			$sheet = $phpExcel->createSheet($n);
			$phpExcel->setActiveSheetIndex($n);
			$phpExcel->getActiveSheet()->setTitle($i);
			$f = file($file);
			foreach ($f as $line => $content) {
				$items = explode('|', $content);
				foreach ($items as $k => $item) {
					$cell = ($cells[$k]) . ($line + 1);
					$phpExcel->getActiveSheet()->setCellValue($cell, $item);
				}
			}
			unset($f);
			$n++;
		}
		$write = new PHPExcel_Writer_Excel2007($phpExcel);
		$fname = date('Ymd').'_report.xlsx';
		$write->save($fname);
	}
}


$ds = DIRECTORY_SEPARATOR;
$fileBase = dirname(__FILE__).$ds.'..'.$ds.'..'.$ds.'..'.$ds;
$files = array(
	'reportDriver_' => $fileBase.date('Ymd').'reportDriver_'.'.json',
	'dayReportDriver_' => $fileBase.date('Ymd').'dayReportDriver_'.'.json',
	'detailFaildHasIDCard_' => $fileBase.date('Ymd').'detailFaildHasIDCard_'.'.json',
	'examNotPass_' => $fileBase.date('Ymd').'examNotPass_'.'.json',
	'unaudited_' => $fileBase.date('Ymd').'unaudited_'.'.json',
);

FileToXls::to($files);
