<?php
if (!Check::post('teamId', 'scoreId')) throw new Exception('no score id given');

if (!Check::isIn($_POST['scoreId'], 'scores'))  throw new Exception('score id not found');
if ($_POST['teamId'] !== 'NULL' && !Check::isIn($_POST['teamId'], 'teams') )  throw new Exception('team id not found');


if ($_POST['teamId'] === 'NULL') {
    $team_id = null;
} else {
    $team_id = $_POST['teamId'];
}
$db->updateRow('scores', $_POST['scoreId'], array('team_id' => $team_id));

$score = FSS::tableRow('scores', $_POST['scoreId']);
$person = FSS::tableRow('persons', $score['person_id']);

$team = null;
if ($score['team_id']) $team = FSS::tableRow('teams', $score['team_id']);

Log::insert('set-score-team', array(
    'person' => $person,
    'score' => $score,
    'team' => $team,
));

$output['success'] = true;
