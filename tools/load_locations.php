<?php

try {
  require_once(__DIR__.'/../includes/lib/init.php');
} catch (Exception $e) {
  die($e->getMessage());
}

$tables = array(
  'team'  => array('short', 1670), 
  'place' => array('name',  207)
);
foreach($tables as $table => $tableConfig) {
  $rows = $db->getRows("
    SELECT *
    FROM `".$table."s`
    WHERE (`lat` IS NULL OR `lat` = '')
    AND id > ".$tableConfig[1]."
    ORDER BY `id`
  ");

  foreach ($rows as $row) {
    echo $row[$tableConfig[0]]." - ".$row['id']."\n";
    $objects = json_decode(file_get_contents('http://nominatim.openstreetmap.org/search?q='.urlencode($row[$tableConfig[0]].', Deutschland').'&format=json'), true);

    if (count($objects) == 0) continue;

    echo "http://www.feuerwehrsport-statistik.de/page/".$table."-".$row['id'].".html\n";

    $db->updateRow($table.'s', $row['id'], array(
      'lat' => trim($objects[0]['lat']),
      'lon' => trim($objects[0]['lon']),
    ), 'id', false);

    usleep(589);
    Map::downloadStaticMap($table.'s', $row['id']) || die("Fehler beim Herunterladen");

    Log::insert('set-'.$table.'-location', array(
      'team' => FSS::tableRow($table.'s', $row['id'])
    ), false);

    usleep(50);
  }
}