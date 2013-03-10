<?php
Title::set('Wettkampf-Typen');

$events = $db->getRows("
    SELECT `e`.*, COUNT(`c`.`id`) AS `count`
    FROM `events` `e`
    INNER JOIN `competitions` `c` ON `c`.`event_id` = `e`.`id`
    GROUP BY `e`.`id`
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
    '<tr><td>'.Link::event($event['id'], $event['name']).'</td><td>',
      $event['count'],
    '</td></tr>';
}

echo '</tbody></table>
    <h2>Diagramme</h2>';

echo '<img src="chart.php?type=events&amp;discipline=2&amp;sex=female" alt=""/>';
echo '<img src="chart.php?type=events&amp;discipline=1&amp;sex=male" alt=""/>';
echo '<img src="chart.php?type=events&amp;discipline=2&amp;sex=male" alt=""/>';
