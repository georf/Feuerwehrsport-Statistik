<?php

Check2::except()->isSubAdmin();
$competitionId = Check2::except()->post('competitionId')->isIn('competitions');
$name   = Check2::except()->post('name')->present();

$resultId = $db->updateRow('competitions', $competitionId, array(
  'name'    => trim($name),
));

Log::insertWithAlert('set-competition-name', FSS::tableRow('competitions', $resultId));
$output['success'] = true;
