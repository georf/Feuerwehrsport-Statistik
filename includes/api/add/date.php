<?php

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

$resultId = $db->insertRow('dates', array(
  'date'        => $date,
  'name'        => $name,
  'place_id'     => $placeId,
  'event_id'     => $eventId,
  'description' => $description,
  'disciplines' => implode(',', $provided)
));

Log::insert('add-date', FSS::tableRow('dates', $resultId));
$output['success'] = true;
