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
    UNION
      SELECT `team_id`,CONCAT('HL',`id`) AS `key`
      FROM `scores`
      WHERE `person_id` = '".$id."'
      AND `discipline` = 'HL'
    UNION
      SELECT `team_id`,CONCAT('GS',`id`) AS `key`
      FROM `scores_gs`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
      OR `person_5` = '".$id."'
      OR `person_6` = '".$id."'
    UNION
      SELECT `team_id`,CONCAT('LA',`id`) AS `key`
      FROM `scores_la`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
      OR `person_5` = '".$id."'
      OR `person_6` = '".$id."'
      OR `person_7` = '".$id."'
    UNION
      SELECT `team_id`,CONCAT('FS',`id`) AS `key`
      FROM `scores_fs`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
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
    UNION
      SELECT `team_id`, 'female' AS `sex`
      FROM `scores_gs`
      WHERE `competition_id` = '".$competitionId."'
    UNION
      SELECT `team_id`, `sex`
      FROM `scores_la`
      WHERE `competition_id` = '".$competitionId."'
    UNION
      SELECT `team_id`, `sex`
      FROM `scores_fs`
      WHERE `competition_id` = '".$competitionId."'
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
