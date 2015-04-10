<?php

Check2::except()->isSubAdmin();

$discipline = Check2::except()->post('discipline')->isIn(array('fs', 'la', 'gs'));
$scoreId = Check2::except()->post('scoreId')->isIn("group_scores");
$teamId  = Check2::except()->post('teamId')->isIn('teams');

$db->updateRow("group_scores", $scoreId, array(
  'team_id' => $teamId
));

self::sendMail('Sub-Admin-Log auf Statistik-Seite (group-score-team)', print_r(array($scoreId, $teamId), true));

$output['success'] = true;
