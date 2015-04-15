<?php
ini_set('memory_limit', '2560M');
require_once 'phpexcel/PHPExcel.php';

define('T_DATE', date('Ymd'));

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
			$fileStream = fopen($file, 'r');
			$line = 1;
			while ($content = fgets($fileStream)) {
				$items = explode('|', $content);
				foreach ($items as $k => $item) {
					$cell = ($cells[$k]) . $line;
					$phpExcel->getActiveSheet()->setCellValue($cell, $item);
				}
				$line++;
			}
			$n++;
		}
		$phpExcel->setActiveSheetIndex(0);
		$write = new PHPExcel_Writer_Excel2007($phpExcel);
		$fname = T_DATE.'_report.xlsx';
		$write->save($fname);
	}
}


$ds = DIRECTORY_SEPARATOR;
$fileBase = dirname(__FILE__).$ds.'..'.$ds.'..'.$ds.'..'.$ds;
$files = array(
	'reportDriver_' => $fileBase.T_DATE.'reportDriver_'.'.json',
	'dayReportDriver_' => $fileBase.T_DATE.'dayReportDriver_'.'.json',
	'detailFaildHasIDCard_' => $fileBase.T_DATE.'detailFaildHasIDCard_'.'.json',
	'examNotPass_' => $fileBase.T_DATE.'examNotPass_'.'.json',
	'unaudited_' => $fileBase.T_DATE.'unaudited_'.'.json',
);

FileToXls::to($files);
