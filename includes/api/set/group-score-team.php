<?php

Check2::except()->isAdmin();

$discipline = Check2::except()->post('discipline')->isIn(array('fs', 'la', 'gs'));
$tableName = 'scores_'.$discipline;
$scoreId = Check2::except()->post('scoreId')->isIn($tableName);
$teamId  = Check2::except()->post('teamId')->isIn('teams');

$db->updateRow($tableName, $scoreId, array(
  'team_id' => $teamId
));

$output['success'] = true;
