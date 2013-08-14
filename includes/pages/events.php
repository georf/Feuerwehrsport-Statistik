<?php

TempDB::generate('x_full_competitions');
$events = $db->getRows("
    SELECT `event_id` AS `id`, `event` AS `name`, COUNT(`id`) AS `count`
    FROM `x_full_competitions`
    GROUP BY `event_id`
");
echo Title::set('Wettkampf-Typen');

echo CountTable::get('event', $events);
