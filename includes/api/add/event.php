<?php

$name = Check2::except()->post('name')->present();

$resultId = $db->insertRow('events', array(
  'name' => trim($name),
));

Log::insert('add-event', FSS::tableRow('events', $resultId));
$output['success'] = true;
