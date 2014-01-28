<?php
$team_id = Check2::except()->post('team_id')->isIn('teams');
$lat = Check2::except()->post('lat')->present();
$lon = Check2::except()->post('lon')->present();

$db->updateRow('teams', $team_id, array(
  'lat' => $lat,
  'lon' => $lon,
));

Map::downloadStaticMap('teams', $team_id);

Log::insert('set-team-location', array(
  'team' => FSS::tableRow('teams', $team_id)
));

$output['success'] = true;
