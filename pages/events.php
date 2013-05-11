<?php
Title::set('Wettkampf-Typen');

$events = $db->getRows("
    SELECT `e`.*, COUNT(`c`.`id`) AS `count`
    FROM `events` `e`
    INNER JOIN `competitions` `c` ON `c`.`event_id` = `e`.`id`
    GROUP BY `e`.`id`
");

TempDB::generate('x_full_competitions');
$events = $db->getRows("
    SELECT `event_id`, `event`, COUNT(`id`) AS `count`
    FROM `x_full_competitions`
    GROUP BY `event_id`
");

echo '<h1>Wettkampf-Typen</h1>
 <table class="datatable">
    <thead>
      <tr>
        <th style="width:80%">Typ</th>
        <th style="width:20%">Wettkämpfe</th>
      </tr>
    </thead>
    <tbody>';

foreach ($events as $event) {
  echo
    '<tr><td>'.Link::event($event['event_id'], $event['event']).'</td><td>',
      $event['count'],
    '</td></tr>';
}

echo '</tbody></table>
    <h2>Diagramme</h2>';

echo '<img src="chart.php?type=events&amp;discipline=HB&amp;sex=female" alt=""/>';
echo '<img src="chart.php?type=events&amp;discipline=HL&amp;sex=male" alt=""/>';
echo '<img src="chart.php?type=events&amp;discipline=HB&amp;sex=male" alt=""/>';
