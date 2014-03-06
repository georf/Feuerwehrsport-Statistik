<?php

TempDB::generate('x_full_competitions');

$places = $db->getRows("
  SELECT `place_id` AS `id`, `place` AS `name`, COUNT(`id`) AS `count`
  FROM `x_full_competitions`
  GROUP BY `place_id`
");

echo Title::set('Wettkampforte');

echo CountTable::get('place', $places);
