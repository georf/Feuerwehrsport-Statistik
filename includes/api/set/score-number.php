<?php

$score_id    = Check2::except()->post('score_id')->isIn('scores');
$team_number = Check2::except()->post('team_number')->isNumber();

$db->updateRow('scores', $score_id, array('team_number' => $team_number));

$score = FSS::tableRow('scores', $score_id);
$person = FSS::tableRow('persons', $score['person_id']);
$team = null;
if ($score['team_id']) $team = FSS::tableRow('teams', $score['team_id']);

Log::insert('set-score-team-number', array(
  'person' => $person,
  'score' => $score,
  'team' => $team,
));
$output['success'] = true;
