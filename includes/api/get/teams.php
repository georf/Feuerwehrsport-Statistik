<?php

$outTeams = array();
$where = '';

$id = Check2::value(false)->post('personId')->isIn('persons');
if ($id !== false) {
  $teams = $db->getRows("
    SELECT `t`.*, COUNT(`i`.`key`) AS `count`
    FROM (
      SELECT `team_id`,CONCAT('HB',`id`) AS `key`
      FROM `scores`
      WHERE `person_id` = '".$id."'
      AND `discipline` = 'HB'
    UNION ALL
      SELECT `team_id`,CONCAT('HL',`id`) AS `key`
      FROM `scores`
      WHERE `person_id` = '".$id."'
      AND `discipline` = 'HL'
    UNION ALL
      SELECT `team_id`,CONCAT(`gst`.`discipline`,`gs`.`id`) AS `key`
      FROM `group_scores` `gs`
      INNER JOIN `person_participations` `p` ON `p`.`score_id` = `gs`.`id`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `p`.`person_id` = '".$id."'
    ) `i`
    INNER JOIN `teams` `t` ON `t`.`id` = `i`.`team_id`
    GROUP BY `team_id`
    ORDER BY `count` DESC
  ");

  foreach ($teams as $team) {
    $outTeams[] = array('value' => $team['id'], 'display' => $team['name'], 'inteam' => true);
    $where .= " AND `t`.`id` != '".$team['id']."' ";
  }
}

$competitionId = Check2::value(false)->post('competitionId')->isIn('competitions');
if ($competitionId !== false) {
  $sex = Check2::value(false)->post('sex')->isSex();
  $whereSex = ($sex !== false)? " WHERE `sex` = '".$sex."' ":'';
  $where .= " AND `t`.`id` IN (
    SELECT `team_id`
    FROM (
      SELECT `team_id`, `p`.`sex`
      FROM `scores` `s`
      INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
      WHERE `competition_id` = '".$competitionId."'
    UNION ALL
      SELECT `team_id`, `sex`
      FROM `group_scores` `gs`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      WHERE `gsc`.`competition_id` = '".$competitionId."'
    ) `i`
    ".$whereSex.") ";
}

$teams = $db->getRows("
  SELECT *
  FROM `teams` `t`
  WHERE 1 = 1
  ".$where."
  ORDER BY `name`
");

foreach ($teams as $team) {
  $outTeams[] = array(
    'value'   => $team['id'], 
    'display' => $team['name'], 
    'inteam'  => false
  );
}

$output['teams'] = $outTeams;
$output['success'] = true;
