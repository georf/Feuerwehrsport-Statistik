#!/usr/bin/php
<?php (PHP_SAPI === 'cli') || exit();

try {
  require_once(__DIR__.'/../includes/lib/init.php');
} catch (Exception $e) {
  die($e->getMessage());
}

foreach (array(
  'best-of',
  'competitions',
  'dates',
  'events',
  'home',
  'logs',
  'news',
  'persons',
  'places',
  'teams',
  'years',
) as $name) {
  file_get_contents("http://www.feuerwehrsport-statistik.de/page/".$name.".html");
}

foreach (array(
  "best-performance-of-year" => "SELECT YEAR(`date`) AS `identifier` FROM `competitions` GROUP BY `identifier`",
  "best-scores-of-year" => "SELECT YEAR(`date`) AS `identifier` FROM `competitions` GROUP BY `identifier`",
  "competition" => "SELECT `id` AS `identifier` FROM `competitions`",
  "date" => "SELECT `id` AS `identifier` FROM `dates`",
  "dcup" => "SELECT `year` AS `identifier` FROM `dcups`",
  "event" => "SELECT `id` AS `identifier` FROM `events`",
  "news" => "SELECT `id` AS `identifier` FROM `news`",
  "person" => "SELECT `id` AS `identifier` FROM `persons`",
  "place" => "SELECT `id` AS `identifier` FROM `places`",
  "team" => "SELECT `id` AS `identifier` FROM `teams`",
  "year" => "SELECT YEAR(`date`) AS `identifier` FROM `competitions` GROUP BY `identifier`",
) as $name => $sql) {
  foreach ($db->getRows($sql, 'identifier') as $identifier) {
    file_get_contents("http://www.feuerwehrsport-statistik.de/page/".$name."-".$identifier.".html");
  }
}
