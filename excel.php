<?php

try {
  require_once(__DIR__.'/includes/lib/init.php');

  $excelFile = Cache::get();
  if (!$excelFile) {
    $excelFile = new PHPExcel();
    require Check2::except()->get('page')->isInPath(__DIR__.'/includes/excel/');
    Cache::put($excelFile);
  }

  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment; filename="Ergebnisse.xlsx"');
  PHPExcel_IOFactory::createWriter($excelFile, 'Excel2007')->save('php://output');
  $filename = Cache::generateFile();
  if ($filename !== false) PHPExcel_IOFactory::createWriter($excelFile, 'Excel2007')->save($filename);
} catch (Exception $e) {
  print_r($e);
}