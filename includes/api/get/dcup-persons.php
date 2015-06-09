<?php

$competitionId = Check2::except()->post('competitionId')->isIn('competitions');

$output['persons'] = $db->getRows("
  SELECT p.*
  FROM scores_dcup_single ds
  INNER JOIN scores s ON `ds`.`score_id` = `s`.`id` AND s.competition_id = ".$competitionId."
  INNER JOIN persons p ON `s`.`person_id` = `p`.`id`
  GROUP BY person_id
  ORDER BY name, firstname
");

$output['success'] = true;
