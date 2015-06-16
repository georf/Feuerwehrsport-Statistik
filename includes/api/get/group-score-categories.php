<?php

$competitionId = Check2::value()->post('competitionId')->isIn('competitions');
$discipline = Check2::value()->post('discipline')->isDiscipline();

$where = array("1 = 1");
if ($discipline) {
  $where[] = "discipline = '".$discipline."'";
}
if ($competitionId) {
  $where[] = "competition_id = '".$competitionId."'";
}

$output['categories'] = $db->getRows("
  SELECT `gsc`.*, gst.name AS type_name
  FROM `group_score_categories` `gsc`
  INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
  WHERE ".implode(" AND ", $where)."
");
$output['success'] = true;
