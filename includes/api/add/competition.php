<?php

Check2::except()->isAdmin();

$date     = Check2::except()->post('date')->isDate();
$place_id = Check2::except()->post('place_id')->isIn('places');
$event_id = Check2::except()->post('event_id')->isIn('events');
$name     = Check2::value('')->post('name')->present();

$result_id = $db->insertRow('competitions', array(
  'date'     => $date,
  'name'     => $name,
  'place_id' => $place_id,
  'event_id' => $event_id,
));

Log::insert('add-competition', FSS::tableRow('competitions', $result_id));
$output['success'] = true;
