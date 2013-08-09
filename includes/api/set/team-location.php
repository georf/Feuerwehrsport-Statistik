<?php
if (!Check::post('team', 'lat', 'lon')) throw new Exception('no valid input');
if (!Check::isIn($_POST['team'], 'teams')) throw new Exception('no valid team');

$db->updateRow('teams', $_POST['team'], array(
    'lat' => $_POST['lat'],
    'lon' => $_POST['lon'],
));

Map::downloadStaticMap('teams', $_POST['team']);

Log::insert('set-team-location', array(
    'team' => FSS::tableRow('teams', $_POST['team'])
));

$output['success'] = true;
