<?php

$output['types'] = $db->getRows("
  SELECT *
  FROM `score_types`
");
$output['success'] = true;
