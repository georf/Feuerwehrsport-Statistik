<?php

$years = $db->getRows("
  SELECT `year`, COUNT(`year`) AS `count`
  FROM (
    SELECT YEAR(`date`) AS `year`
    FROM `competitions`
  ) `i`
  GROUP BY `year`
  ORDER BY `year`
");

echo Title::set('Jahre');

echo CountTable::get('year', $years, 'year');
