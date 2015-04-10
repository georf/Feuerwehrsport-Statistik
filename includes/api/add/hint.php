<?php

Check2::except()->isSubAdmin();
$hint = Check2::except()->post('hint')->present();
$competitionId = Check2::except()->post('competitionId')->isIn('competitions');

$resultId = $db->insertRow('competition_hints', array(
  'hint' => trim($hint),
  'competition_id' => $competitionId,
));

Log::insertWithAlert('add-hint', FSS::tableRow('competition_hints', $resultId));
$output['success'] = true;
