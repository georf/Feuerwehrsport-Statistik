<?php

$competition_id = Check2::except()->post('competition_id')->isIn('competitions');
$score_type_id  = Check2::except()->post('score_type_id')->isIn('score_types', true);

$db->updateRow('competitions', $competition_id, array(
  'score_type_id' => $score_type_id
));

Log::insert('set-score-type', array(
  'competition' => FSS::tableRow('competitions', $competition_id)
));

$output['success'] = true;
