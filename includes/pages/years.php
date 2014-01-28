<?php

$years = $db->getRows("
  SELECT `year`, COUNT(`year`) AS `count`
  FROM (
    SELECT YEAR( `c`.`date` ) AS `year`
    FROM (
      SELECT `competition_id`
      FROM `scores`
      UNION 
      SELECT `competition_id`
      FROM `scores_fs`
      UNION 
      SELECT `competition_id`
      FROM `scores_la`
      UNION 
      SELECT `competition_id`
      FROM `scores_gs`
    ) `s`
    INNER JOIN `competitions` `c` ON `c`.`id` = s.`competition_id`
  ) `i`
  GROUP BY `year`
  ORDER BY `year`
");

echo Title::set('Jahre');

echo CountTable::get('year', $years, 'year');
