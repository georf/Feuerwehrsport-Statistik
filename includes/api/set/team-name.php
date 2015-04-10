<?php

Check2::except()->isSubAdmin();
$team   = Check2::except()->post('teamId')->isIn('teams', 'row');
$name   = Check2::except()->post('name')->present();
$short  = Check2::except()->post('short')->present();
$type   = Check2::except()->post('type')->present();

$db->updateRow('teams', $team['id'], array(
  'name' => trim($name),
  'short' => trim($short),
  'type' => trim($type),
));


Log::insertWithAlert('set-team-name', array(
  "old" => $team,
  "new" => FSS::tableRow('teams', $team['id'])
));
$output['success'] = true;
