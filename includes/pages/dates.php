<?php

$dates = $db->getRows("
  SELECT
    `d`.`id`,`d`.`date`,`d`.`name`,`d`.`description`,`d`.`place_id`,
    `d`.`event_id`,
    `p`.`name` AS `place`, `d`.`disciplines`,`e`.`name` AS `event`
  FROM `dates` `d`
  LEFT JOIN `places` `p` ON `p`.`id` = `d`.`place_id`
  LEFT JOIN `events` `e` ON `e`.`id` = `d`.`event_id`
  WHERE `date` > NOW()
  ORDER BY `date` DESC
  LIMIT 10;
");
echo '<span style="float:right"><button id="add-date">Termin hinzufügen</button></span>';
echo Title::set('Wettkampf-Termine');

echo Bootstrap::row()->col(CountTable::build($dates)
->col('Datum', 'date', 9)
->col('Veranstaltung', 'name', 32)
->col('Ort', function ($date) {
  return (empty($date['place_id']))? '' : Link::place($date['place_id'], $date['place']);
}, 13)
->col('Typ', function ($date) {
  return (empty($date['event_id']))? '' : Link::event($date['event_id'], $date['event']);
}, 13)
->col('Disziplinen', function ($date) {
  $disciplines = explode(',', $date['disciplines']);
  sort($disciplines);
  foreach ($disciplines as $k => $dis) {
      $disciplines[$k] = FSS::dis2img(strtolower($dis));
  }
  return implode(' ', $disciplines);
}, 15)
->col('', function ($date) { return Link::date($date['id']); }, 8)
,12);