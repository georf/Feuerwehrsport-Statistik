<?php

$output['places'] = $db->getRows("
  SELECT `name`,`id`
  FROM `places`
  ORDER BY `name`
");
$output['success'] = true;
