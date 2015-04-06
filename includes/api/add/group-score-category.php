<?php

Check2::except()->isAdmin();

$name = Check2::except()->post('name')->present();
$competitionId = Check2::except()->post('competitionId')->isIn('competitions');
$groupScoreTypeId = Check2::except()->post('groupScoreTypeId')->isIn('group_score_types');

$db->insertRow('group_score_categories', array(
  'name' => trim($name),
  'competition_id' => $competitionId,
  'group_score_type_id' => $groupScoreTypeId,
));

$output['success'] = true;
