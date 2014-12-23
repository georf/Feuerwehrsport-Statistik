<?php

Check2::except()->isAdmin();
$competitionId = Check2::except()->post('competitionId')->isIn('competitions');
$published     = Check2::except()->post('published')->isIn(array('0', '1', '2'));

$db->updateRow('competitions', $competitionId, array(
  'published' => $published,
), 'id', false);

$output['success'] = true;
