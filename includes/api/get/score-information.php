<?php
$scoreId   = Check2::except()->post('scoreId')->present();
$discipline = Check2::except()->post('discipline')->isIn(array('zk', 'fs', 'gs', 'la'));

$score = false;
$scores = false;

if ($discipline === 'zk') {
  $score = Check2::except()->post('scoreId')->isIn('scores', 'row');
  $scores = $db->getRows("
    SELECT *
    FROM `scores`
    WHERE `person_id` = '".$score['person_id']."'
    AND `competition_id` = '".$score['competition_id']."'
  ");
} elseif ($discipline === 'gs') {
  $score = Check2::except()->post('scoreId')->isIn('scores_gs', 'row');
  $scores = $db->getRows("
    SELECT *
    FROM `scores_gs`
    WHERE `team_id` = '".$score['team_id']."'
    AND `team_number` = '".$score['team_number']."'
    AND `competition_id` = '".$score['competition_id']."'
  ");
} elseif ($discipline === 'fs') {
  $score = Check2::except()->post('scoreId')->isIn('scores_fs', 'row');
  $scores = $db->getRows("
    SELECT *
    FROM `scores_fs`
    WHERE `team_id` = '".$score['team_id']."'
    AND `team_number` = '".$score['team_number']."'
    AND `sex` = '".$score['sex']."'
    AND `competition_id` = '".$score['competition_id']."'
  ");
} elseif ($discipline === 'la') {
  $score = Check2::except()->post('scoreId')->isIn('scores_la', 'row');
  $scores = $db->getRows("
    SELECT *
    FROM `scores_la`
    WHERE `team_id` = '".$score['team_id']."'
    AND `team_number` = '".$score['team_number']."'
    AND `sex` = '".$score['sex']."'
    AND `competition_id` = '".$score['competition_id']."'
  ");
}

if ($scores === false) throw new Exception();

$score['timeHuman'] = FSS::time($score['time']);
$output['score'] = $score;

foreach ($scores as $discipline => $score) {
    $scores[$discipline]['timeHuman'] = FSS::time($score['time']);
}
$output['scores'] = $scores;
$output['success'] = true;
