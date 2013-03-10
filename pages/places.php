<?php



$places = $db->getRows("
    SELECT `p`.*, COUNT(`c`.`id`) AS `count`
    FROM `places` `p`
    INNER JOIN `competitions` `c` ON `c`.`place_id` = `p`.`id`
    GROUP BY `p`.`id`
");

Title::set('Wettkampforte');
echo '
<h1>Wettkampforte</h1>
 <table class="datatable">
    <thead>
      <tr>
        <th style="width:80%">Ort</th>
        <th style="width:20%">Wettkämpfe</th>
      </tr>
    </thead>
    <tbody>';

foreach ($places as $place) {
    echo
    '<tr><td>'.Link::place($place['id'], $place['name']).'</td><td>',
      $place['count'],
    '</td></tr>';
}

echo '</tbody></table>';

