<?php

Check2::except()->isSubAdmin();

$dateId      = Check2::except()->post('dateId')->isIn('dates');
$date        = Check2::except()->post('date')->isDate();
$placeId     = Check2::except()->post('placeId')->isIn('places', true);
$eventId     = Check2::except()->post('eventId')->isIn('events', true);
$name        = Check2::except()->post('name')->present();
$description = Check2::except()->post('description')->present();

$provided = array();
foreach (FSS::$disciplines as $dis) {
  if (Check2::value()->post($dis)->getVal() == 'true') {
    $provided[] = strtoupper($dis);
  }
}
sort($provided);

$db->updateRow('dates', $dateId, array(
  'date'        => trim($date),
  'name'        => trim($name),
  'place_id'    => $placeId,
  'event_id'    => $eventId,
  'description' => trim($description),
  'disciplines' => trim(implode(',', $provided))
));

Log::insertWithAlert('set-date', FSS::tableRow('dates', $dateId));
$output['success'] = true;
