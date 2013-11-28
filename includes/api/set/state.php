<?php
if (!Check::post('for', 'id', 'state')) throw new Exception('no valid input');
if (!in_array($_POST['for'], array('team'))) throw new Exception('no valid input');
if (!Check::isIn($_POST['id'], 'teams')) throw new Exception('no valid team');

$state = (FSS::stateToText($_POST['state']) == $_POST['state'])? NULL : $_POST['state'];


$db->updateRow('teams', $_POST['id'], array(
    'state' => $state
));

Log::insert('set-team-state', array(
    'team' => FSS::tableRow('teams', $_POST['id'])
));

$output['success'] = true;
