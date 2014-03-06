<?php

$competitionId = Check2::except()->post('competitionId')->isIn('competitions');
$scoreTypeId   = Check2::except()->post('scoreTypeId')->isIn('score_types', true);

$db->updateRow('competitions', $competitionId, array(
  'score_type_id' => $scoreTypeId
));

Log::insert('set-score-type', array(
  'competition' => FSS::tableRow('competitions', $competitionId)
));

$output['success'] = true;
