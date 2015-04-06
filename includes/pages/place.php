<?php

$place = Check2::page()->get('id')->isIn('places', 'row');
$id = $place['id'];

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hlf');
TempDB::generate('x_scores_hlm');
TempDB::generate('x_full_competitions');


$competitions = $db->getRows("
  SELECT 
    `id`,
    `date`,
    `event_id`, `event`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `x_scores_hbm`
      WHERE `competition_id` = `c`.`id`
    ) AS `hbm`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `x_scores_hbf`
      WHERE `competition_id` = `c`.`id`
    ) AS `hbf`,
    (
      SELECT COUNT(*) AS `count`
      FROM `group_scores` `gs`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `gsc`.`competition_id` = `c`.`id`
      AND `gst`.`discipline` = 'GS'
    ) AS `gs`,
    (
      SELECT COUNT(*) AS `count`
      FROM `group_scores` `gs`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `gsc`.`competition_id` = `c`.`id`
      AND `gst`.`discipline` = 'LA'
      AND `sex` = 'female'
    ) AS `laf`,
    (
      SELECT COUNT(*) AS `count`
      FROM `group_scores` `gs`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `gsc`.`competition_id` = `c`.`id`
      AND `gst`.`discipline` = 'LA'
      AND `sex` = 'male'
    ) AS `lam`,
    (
      SELECT COUNT(*) AS `count`
      FROM `group_scores` `gs`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `gsc`.`competition_id` = `c`.`id`
      AND `gst`.`discipline` = 'FS'
      AND `sex` = 'female'
    ) AS `fsf`,
    (
      SELECT COUNT(*) AS `count`
      FROM `group_scores` `gs`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `gsc`.`competition_id` = `c`.`id`
      AND `gst`.`discipline` = 'FS'
      AND `sex` = 'male'
    ) AS `fsm`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `x_scores_hlf`
      WHERE `competition_id` = `c`.`id`
    ) AS `hlf`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `x_scores_hlm`
      WHERE `competition_id` = `c`.`id`
    ) AS `hlm`,
    `la`, `fs`
  FROM `x_full_competitions` `c`
  WHERE `place_id` = '".$id."'
  ORDER BY `date` DESC
");

echo Bootstrap::row()
  ->col(Title::set($place['name']), 9)
  ->col(TableOfContents::get()
    ->link('wettkaempfe', 'Wettkämpfe')
    ->link('auswertung', 'Auswertung')
    ->link('bestzeiten', 'Bestzeiten')
  , 3);

$empty = array();
$small = array('class' => 'small');

echo Title::h2('Wettkämpfe', 'wettkaempfe');
echo Bootstrap::row()->col(CountTable::build($competitions)
->col('Datum', 'date', 8)
->col('Typ', function ($row) { return Link::event($row['event_id'], $row['event']); }, 16)
->col('HBw', function ($row) { return FSS::countNoEmpty($row['hbf']); }, 5, $empty, $small) 
->col('HBm', function ($row) { return FSS::countNoEmpty($row['hbm']); }, 5, $empty, $small)
->col('GS', function ($row) { return FSS::countNoEmpty($row['gs']); }, 5, $empty, $small)
->col('LAw', function ($row) { return FSS::countNoEmpty($row['laf']); }, 5, array('title' => function ($row) { return FSS::laType($row['la']); }), $small)
->col('LAm', function ($row) { return FSS::countNoEmpty($row['lam']); }, 5, array('title' => function ($row) { return FSS::laType($row['la']); }), $small)
->col('FSw', function ($row) { return FSS::countNoEmpty($row['fsf']); }, 5, array('title' => function ($row) { return FSS::fsType($row['fs']); }), $small)
->col('FSm', function ($row) { return FSS::countNoEmpty($row['fsm']); }, 5, array('title' => function ($row) { return FSS::fsType($row['fs']); }), $small)
->col('HLw', function ($row) { return FSS::countNoEmpty($row['hlf']); }, 5, $empty, $small)
->col('HLm', function ($row) { return FSS::countNoEmpty($row['hlm']); }, 5, $empty, $small)
->col('', function ($row) { return Link::competition($row['id'], 'Info'); }, 5)
, 12);

echo Title::h2('Auswertung', 'auswertung');
echo Analysis::generalCharts('place', $id);

echo Title::h2('Bestzeiten', 'bestzeiten');
echo Analysis::bestOfYears('place', $id);

echo Title::h2("Karte", "karte");
if (Map::isFile('places', $id)) {
  echo Bootstrap::row()
    ->col(Map::getImg('places', $id), 8)
    ->col('<button id="map-load" data-place-id="'.$id.'" data-place-name="'.htmlspecialchars($place['name']).'" data-lat="'.$place['lat'].'" data-lon="'.$place['lon'].'">Interaktive Karte laden</button>', 4);
} else {
  echo Bootstrap::row()
    ->col('<img src="/styling/images/no-location.png" alt=""/><br/>Keine Kartenposition vorhanden', 8)
    ->col('<button id="map-load" data-place-id="'.$id.'" data-place-name="'.htmlspecialchars($place['name']).'">Interaktive Karte zum Bearbeiten laden</button>', 4);
}
echo Bootstrap::row('hide')
->col('<div id="map-dynamic"></div>', 8)
->col('<button id="map-edit">Position bearbeiten</button><button id="map-save">Speichern</button><p id="map-edit-hint">Bitte den Marker auf die korrekte Position ziehen.</p>', 4);
