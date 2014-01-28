<?php

$where = '';
$sex = Check2::value(false)->post('sex')->isSex();
if ($sex) {
  $where = " WHERE `sex`  = '".$sex."' ";
}

$output['persons'] = $db->getRows("
  SELECT `name`,`firstname`,`id`,`sex`
  FROM `persons`
  ".$where."
  ORDER BY `name`, `firstname`
");
$output['success'] = true;
