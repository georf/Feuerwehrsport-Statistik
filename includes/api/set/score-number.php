<?php
if (!Check::post('teamNumber', 'scoreId')) throw new Exception('no score id given');


if (!Check::isIn($_POST['scoreId'], 'scores'))  throw new Exception('score id not found');
if (!is_numeric($_POST['teamNumber']))  throw new Exception('team number not found');

$id = $_POST['scoreId'];

$db->updateRow('scores', $id, array('team_number' => trim($_POST['teamNumber'])));


$score = FSS::tableRow('scores', $id);
$person = FSS::tableRow('persons', $score['person_id']);
$team = null;
if ($score['team_id']) $team = FSS::tableRow('teams', $score['team_id']);

Log::insert('set-score-team-number', array(
    'person' => $person,
    'score' => $score,
    'team' => $team,
));

$output['success'] = true;
