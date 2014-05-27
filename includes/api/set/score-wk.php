<?php

$discipline = Check2::except()->post('discipline')->isDiscipline();

switch ($discipline) {
  case 'gs':
    $table = 'gs';
    $wks = 7;
    break;
  case 'fs':
    $table = 'fs';
    $wks = 5;
    break;
  case 'la';
    $table = 'la';
    $wks = 8;
    break;
  default:
    throw new Exception('Bad discipline');
}
$score = Check2::except()->post('scoreId')->isIn("scores_".$table, 'row');

$update = false;
$updateSqlSelect = array();
$updateSqlJoin = array();
for ($i = 1; $i < $wks; $i++) {
  $participation = $db->getFirstRow("
    SELECT `id`,`person_id`
    FROM `person_participations_".$table."`
    WHERE `score_id` = ".$score['id']."
    AND `position` = ".$i."
    LIMIT 1");
  $personId = Check2::value()->post('person'.$i)->isIn('persons', true);
  if (!$personId && Check2::boolean()->post('person'.$i)->match('|^NULL$|') && $participation !== false) {
    $db->deleteRow("person_participations_".$table, $participation['id']);
    $update = true;
  } elseif ($personId !== null && $participation !== false && $participation['person_id'] != $personId) {
    $db->updateRow("person_participations_".$table, $participation['id'], array(
      'person_id' => $personId
    ));
    $update = true;
  } elseif ($personId !== null && $participation === false) {
    $db->insertRow("person_participations_".$table, array(
      'person_id' => $personId,
      'score_id' => $score['id'],
      'position' => $i,
    ));
    $update = true;
  }
  $updateSqlSelect[] = "`p".$i."`.`person_id` AS `person_".$i."`";
  $updateSqlJoin[] = "LEFT JOIN `person_participations_".$table."` `p".$i."` ON `p".$i."`.`score_id` =  `s`.`id` AND `p".$i."`.`position` = ".$i." ";
}

if ($update) {
  $score = $db->getFirstRow("
    SELECT 
    `s`.`id`,`team_id`,`team_number`,`competition_id`,`time`,
    ".implode(", ", $updateSqlSelect)."
    FROM `scores_gs` `s`
    ".implode($updateSqlJoin)."
    WHERE `s`.`id` = '".$score['id']."'");
  Log::insert('set-score-wk', array(
    'key' => $discipline,
    'score' => $score,
    'competition' => FSS::competition($score['competition_id'])
  ));
}

$output['success'] = true;
