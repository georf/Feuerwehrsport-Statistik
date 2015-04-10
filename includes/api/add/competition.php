<?php

Check2::except()->isSubAdmin();

$date    = Check2::except()->post('date')->isDate();
$placeId = Check2::except()->post('placeId')->isIn('places');
$eventId = Check2::except()->post('eventId')->isIn('events');
$name    = Check2::value('')->post('name')->present();

$resultId = $db->insertRow('competitions', array(
  'date'     => trim($date),
  'name'     => trim($name),
  'place_id' => $placeId,
  'event_id' => $eventId,
));

Log::insertWithAlert('add-competition', FSS::tableRow('competitions', $resultId));
$output['success'] = true;
