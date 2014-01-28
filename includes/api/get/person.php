<?php

$person_id = Check2::except()->post('person_id')->isIn('persons');
$output['person'] = $db->getFirstRow("
  SELECT *
  FROM `persons`
  WHERE `id` = '".$person_id."'
  LIMIT 1");
$output['success'] = true;
