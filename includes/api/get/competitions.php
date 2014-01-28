<?php

TempDB::generate('x_full_competitions');

$output['competitions'] = $db->getRows("
  SELECT *
  FROM `x_full_competitions`
  ORDER BY `date`, `event`, `place`
");
$output['success'] = true;
