<?php

Check2::page()->isAdmin();

$fh = opendir($config['logo-path']);
while ($file = readdir($fh)) {
  if (is_file($config['logo-path'].$file)) {
    $rows = $db->getRows("
      SELECT *
      FROM `teams`
      WHERE `logo` = '".$db->escape($file)."'
    ");
    if (!count($rows)) {
      echo $file."<br/>";
      unlink($config['logo-path'].$file);
    }
  }
}
