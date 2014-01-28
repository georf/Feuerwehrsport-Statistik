<?php

$score_id    = Check2::except()->post('score_id')->isIn('scores');
$team_id = Check2::except()->post('team_id')->isIn('teams', true);

$db->updateRow('scores', $score_id, array(
  'team_id' => $team_id
));

$score = FSS::tableRow('scores', $score_id);
$person = FSS::tableRow('persons', $score['person_id']);

$team = null;
if ($score['team_id']) $team = FSS::tableRow('teams', $score['team_id']);

Log::insert('set-score-team', array(
  'person' => $person,
  'score' => $score,
  'team' => $team,
));

$output['success'] = true;
