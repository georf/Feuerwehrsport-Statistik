<?php

$competitionId = Check2::except()->post('competitionId')->isIn('competitions');

$output['hints'] = $db->getRows("SELECT * FROM `competition_hints` WHERE `competition_id` = ".$competitionId);
$output['success'] = true;
