<?php
$placeId = Check2::except()->post('placeId')->isIn('places');
$lat = Check2::except()->post('lat')->present();
$lon = Check2::except()->post('lon')->present();

$db->updateRow('places', $placeId, array(
  'lat' => trim($lat),
  'lon' => trim($lon),
));

Map::downloadStaticMap('places', $placeId);

Log::insert('set-place-location', array(
  'place' => FSS::tableRow('places', $placeId)
));

$output['success'] = true;
