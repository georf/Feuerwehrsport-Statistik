<?php

Check2::except()->isAdmin();

$discipline = Check2::except()->post('discipline')->isIn(array('fs', 'la', 'gs'));
$scoreId = Check2::except()->post('scoreId')->isIn("group_scores");
$teamId  = Check2::except()->post('teamId')->isIn('teams');

$db->updateRow("group_scores", $scoreId, array(
  'team_id' => $teamId
));

$output['success'] = true;
