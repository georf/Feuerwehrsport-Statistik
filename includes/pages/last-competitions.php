<?php

TempDB::generate('x_full_competitions');

$competitions = $db->getRows("
  SELECT *
  FROM `x_full_competitions`
  ORDER BY `id` DESC
");

foreach ($competitions as $competition) {
  echo $competition['event']." - ".$competition['place']." - ".gDate($competition['date']);
  if (!empty($competition['name'])) echo ' ('.$competition['name'].")";
  echo '<br/>';
  echo "http://www.feuerwehrsport-statistik.de/page/competition-".$competition['id'].".html<br/>";
}