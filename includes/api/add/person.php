<?php

$name      = Check2::except()->post('name')->present();
$firstname = Check2::except()->post('firstname')->present();
$sex       = Check2::except()->post('sex')->isSex();

$result_id = $db->insertRow('persons', array(
  'name'      => $name,
  'firstname' => $firstname,
  'sex'       => $sex,
));

Log::insert('add-person', FSS::tableRow('persons', $result_id));
$output['success'] = true;
