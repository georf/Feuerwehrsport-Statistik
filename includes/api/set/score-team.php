<?php

$scoreId = Check2::except()->post('scoreId')->isIn('scores');
$teamId  = Check2::except()->post('teamId')->isIn('teams', true);

$db->updateRow('scores', $scoreId, array(
  'team_id' => $teamId
));

$score = FSS::tableRow('scores', $scoreId);
$person = FSS::tableRow('persons', $score['person_id']);

$team = null;
if ($score['team_id']) $team = FSS::tableRow('teams', $score['team_id']);

Log::insert('set-score-team', array(
  'person' => $person,
  'score' => $score,
  'team' => $team,
));

$output['success'] = true;
