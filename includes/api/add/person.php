<?php

$name      = Check2::except()->post('name')->present();
$firstname = Check2::except()->post('firstname')->present();
$sex       = Check2::except()->post('sex')->isSex();

$resultId = $db->insertRow('persons', array(
  'name'      => trim($name),
  'firstname' => trim($firstname),
  'sex'       => trim($sex),
));

Log::insert('add-person', FSS::tableRow('persons', $resultId));
$output['success'] = true;
