<?php

TempDB::generate('x_full_competitions');


$places = $db->getRows("
    SELECT `place_id`, `place`, COUNT(`id`) AS `count`
    FROM `x_full_competitions`
    GROUP BY `place_id`
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
    '<tr><td>'.Link::place($place['place_id'], $place['place']).'</td><td>',
      $place['count'],
    '</td></tr>';
}

echo '</tbody></table>';

