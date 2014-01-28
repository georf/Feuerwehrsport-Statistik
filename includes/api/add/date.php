<?php

$date        = Check2::except()->post('date')->isDate();
$place_id    = Check2::except()->post('place_id')->isIn('places', true);
$event_id    = Check2::except()->post('event_id')->isIn('events', true);
$name        = Check2::except()->post('name')->present();
$description = Check2::except()->post('description')->present();

$provided = array();
foreach (FSS::$disciplines as $dis) {
  if (Check2::value()->post($dis)->getVal() == 'true') {
    $provided[] = strtoupper($dis);
  }
}
sort($provided);

$result_id = $db->insertRow('dates', array(
  'date'        => $date,
  'name'        => $name,
  'place_id'    => $place_id,
  'event_id'    => $event_id,
  'description' => $description,
  'disciplines' => implode(',', $provided)
));

Log::insert('add-date', FSS::tableRow('dates', $result_id));
$output['success'] = true;
