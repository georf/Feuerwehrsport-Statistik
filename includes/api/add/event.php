<?php

$name = Check2::except()->post('name')->present();

$result_id = $db->insertRow('events', array(
  'name' => $name,
));

Log::insert('add-event', FSS::tableRow('events', $result_id));
$output['success'] = true;
