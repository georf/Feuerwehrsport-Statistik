<?php
$teamId = Check2::except()->post('teamId')->isIn('teams');
$lat = Check2::except()->post('lat')->present();
$lon = Check2::except()->post('lon')->present();

$db->updateRow('teams', $teamId, array(
  'lat' => trim($lat),
  'lon' => trim($lon),
));

Map::downloadStaticMap('teams', $teamId);

Log::insert('set-team-location', array(
  'team' => FSS::tableRow('teams', $teamId)
));

$output['success'] = true;
