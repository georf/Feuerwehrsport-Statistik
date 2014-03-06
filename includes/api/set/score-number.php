<?php

$scoreId    = Check2::except()->post('scoreId')->isIn('scores');
$teamNumber = Check2::except()->post('teamNumber')->isNumber();

$db->updateRow('scores', $scoreId, array('team_number' => $teamNumber));

$score = FSS::tableRow('scores', $scoreId);
$person = FSS::tableRow('persons', $score['person_id']);
$team = null;
if ($score['team_id']) $team = FSS::tableRow('teams', $score['team_id']);

Log::insert('set-score-team-number', array(
  'person' => $person,
  'score' => $score,
  'team' => $team,
));
$output['success'] = true;
