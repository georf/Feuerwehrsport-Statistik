<?php

$name = Check2::except()->post('name')->present();

$resultId = $db->insertRow('places', array(
  'name' => $name,
));

Log::insert('add-place', FSS::tableRow('places', $resultId));
$output['success'] = true;
