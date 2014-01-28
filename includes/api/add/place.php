<?php

$name = Check2::except()->post('name')->present();

$result_id = $db->insertRow('places', array(
  'name' => $name,
));

Log::insert('add-place', FSS::tableRow('places', $result_id));
$output['success'] = true;
