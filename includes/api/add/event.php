<?php

Check2::except()->isSubAdmin();
$name = Check2::except()->post('name')->present();

$resultId = $db->insertRow('events', array(
  'name' => trim($name),
));

Log::insertWithAlert('add-event', FSS::tableRow('events', $resultId));
$output['success'] = true;
