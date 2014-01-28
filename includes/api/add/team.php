<?php

$name  = Check2::except()->post('name')->present();
$type  = Check2::except()->post('type')->isIn(array('Team', 'Feuerwehr'));
$short = Check2::except()->post('short')->present();

$result_id = $db->insertRow('teams', array(
  'name'  => $name,
  'short' => $short,
  'type'  => $type,
));

Log::insert('add-team', FSS::tableRow('teams', $result_id));
$output['success'] = true;
