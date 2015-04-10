<?php

Check2::page()->isAdmin();

if (Check::get('id') && Check::isIn($_GET['id'], 'result_files')) {

  $row = $db->getFirstRow("
    SELECT *
    FROM `result_files`
    WHERE `id` = '".$db->escape($_GET['id'])."'
    LIMIT 1;
  ");
  
  // delete file
  unlink($config['file-path'].''.$row['competition_id'].'/'.$row['name']);
  
  // del db entry
  $db->deleteRow('result_files', $_GET['id']);
}

$rows = $db->getRows("
  SELECT *
  FROM `result_files`
  ORDER BY `competition_id` DESC
");

$competition_id = 0;

echo '<table class="table">';

foreach ($rows as $row) {
  if ($competition_id != $row['competition_id']) {
    $competition_id = $row['competition_id'];
    $competition = FSS::competition($row['competition_id']);
    echo '<tr><th>'.$competition['event'].'</th><th>'.$competition['place'].'</th><th>'.$competition['date'].'</th></tr>';
  }
  echo '<tr><td>'.$row['id'].' <a href="/?page=administration&amp;admin=manage_files&amp;id='.$row['id'].'" onclick="return confirm(\'wirklich?\');">Löschen</a></td><td>'.$row['name'].'</td><td>'.$row['content'].'</td></tr>';
}

echo '</table>';
