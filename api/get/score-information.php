<?php
if (!Check::post('key', 'scoreId')) throw new Exception('no score id given');

$score = false;
$scores = false;

if ($_POST['key'] === 'zk') {
    if (!Check::isIn($_POST['scoreId'], 'scores'))  throw new Exception('score id not found');

    $score = FSS::tableRow('scores', $_POST['scoreId']);

    $scores = $db->getRows("
        SELECT *
        FROM `scores`
        WHERE `person_id` = '".$score['person_id']."'
        AND `competition_id` = '".$score['competition_id']."'
    ");
} elseif ($_POST['key'] === 'gs') {
    if (!Check::isIn($_POST['scoreId'], 'scores_gruppenstafette'))  throw new Exception('score id not found');

    $score = FSS::tableRow('scores_gruppenstafette', $_POST['scoreId']);

    $scores = $db->getRows("
        SELECT *
        FROM `scores_gruppenstafette`
        WHERE `team_id` = '".$score['team_id']."'
        AND `team_number` = '".$score['team_number']."'
        AND `competition_id` = '".$score['competition_id']."'
    ");
} elseif ($_POST['key'] === 'fs') {
    if (!Check::isIn($_POST['scoreId'], 'scores_fs'))  throw new Exception('score id not found');

    $score = FSS::tableRow('scores_fs', $_POST['scoreId']);

    $scores = $db->getRows("
        SELECT *
        FROM `scores_fs`
        WHERE `team_id` = '".$score['team_id']."'
        AND `team_number` = '".$score['team_number']."'
        AND `sex` = '".$score['sex']."'
        AND `competition_id` = '".$score['competition_id']."'
    ");
} elseif ($_POST['key'] === 'la') {
    if (!Check::isIn($_POST['scoreId'], 'scores_la'))  throw new Exception('score id not found');

    $score = FSS::tableRow('scores_la', $_POST['scoreId']);

    $scores = $db->getRows("
        SELECT *
        FROM `scores_la`
        WHERE `team_id` = '".$score['team_id']."'
        AND `team_number` = '".$score['team_number']."'
        AND `sex` = '".$score['sex']."'
        AND `competition_id` = '".$score['competition_id']."'
    ");
}

if ($score === false || $scores === false) throw new Exception();

$score['timeHuman'] = FSS::time($score['time']);
$output['score'] = $score;

foreach ($scores as $key => $score) {
    $scores[$key]['timeHuman'] = FSS::time($score['time']);
}
$output['scores'] = $scores;
$output['success'] = true;
