<?php

$for   = Check2::except()->post('for')->isIn(array('team'));
$table = $for.'s';
$id    = Check2::except()->post('id')->isIn($table);
$state = Check2::except()->post('state')->getVal();
$state = (FSS::stateToText($state) == $state)? NULL : $state;

$db->updateRow($table, $id, array(
  'state' => trim($state)
));

Log::insert('set-'.$for.'-state', array(
  'team' => FSS::tableRow($table, $id)
));

$output['success'] = true;
