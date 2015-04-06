<?php

$discipline = Check2::value()->post('discipline')->isDiscipline();

$where = array("1 = 1");
if ($discipline) {
  $where[] = "discipline = '".$discipline."'";
}

$output['types'] = $db->getRows("
  SELECT *
  FROM `group_score_types`
  WHERE ".implode(" AND ", $where)."
");
$output['success'] = true;
