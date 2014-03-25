<?php

$discipline = Check2::except()->post('discipline')->isDiscipline();

switch ($discipline) {
  case 'gs':
    $table = 'scores_gs';
    $wks = 7;
    break;
  case 'fs':
    $table = 'scores_fs';
    $wks = 5;
    break;
  case 'la';
    $table = 'scores_la';
    $wks = 8;
    break;
  default:
    throw new Exception('Bad discipline');
}
$score = Check2::except()->post('scoreId')->isIn($table, 'row');

$update = false;
for ($i = 1; $i < $wks; $i++) {
  $personId = Check2::value()->post('person'.$i)->isIn('persons', true);
  if (!$personId && Check2::boolean()->post('person'.$i)->match('|NULL|')) {
    $personId = null;
  }
  if ($personId !== false && $score['person_'.$i] != $personId) {
    $db->updateRow($table, $score['id'], array(
      'person_'.$i => $personId
    ));
    $update = true;
  }
}

if ($update) {
  $score = FSS::tableRow($table, $score['id']);
  Log::insert('set-score-wk', array(
    'key' => $discipline,
    'score' => $score,
    'competition' => FSS::competition($score['competition_id'])
  ));
}

$output['success'] = true;
