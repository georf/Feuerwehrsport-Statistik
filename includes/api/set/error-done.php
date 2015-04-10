<?php

Check2::except()->isSubAdmin();
$errorId = Check2::except()->post('errorId')->isIn('errors');

$resultId = $db->updateRow('errors', $errorId, array(
  'done_at' => date('Y-m-d H:i:s'),
));

$output['success'] = true;
