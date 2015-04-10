<?php

Check2::except()->isSubAdmin();

$output['errors'] = array_map(function($element) { 
    $element['content'] = unserialize($element['content']);
    return $element;
  }, 
  $db->getRows("
  SELECT `e`.*, `u`.`email`, `u`.`name`
  FROM `errors` `e`
  INNER JOIN `users` `u` ON `u`.`id` = `e`.`user_id`
  ORDER BY `created_at` DESC
"));
$output['success'] = true;
