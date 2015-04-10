<?php

Check2::except()->isSubAdmin();
$person    = Check2::except()->post('personId')->isIn('persons', 'row');
$name      = Check2::except()->post('name')->present();
$firstname = Check2::except()->post('firstname')->present();

$db->updateRow('persons', $person['id'], array(
  'name'    => trim($name),
  'firstname'    => trim($firstname),
));

Log::insertWithAlert('set-person-name', array(
  "old" => $person,
  "new" => FSS::tableRow('persons', $person['id'])
));
$output['success'] = true;
