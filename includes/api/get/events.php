<?php

$output['events'] = $db->getRows("
  SELECT `name`,`id`
  FROM `events`
  ORDER BY `name`
");
$output['success'] = true;
