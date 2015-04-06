<?php

Check2::except()->isAdmin();

$name = Check2::except()->post('name')->present();
$discipline = Check2::except()->post('discipline')->isDiscipline();

$db->insertRow('group_score_types', array(
  'name' => trim($name),
  'discipline' => trim($discipline),
));

$output['success'] = true;
