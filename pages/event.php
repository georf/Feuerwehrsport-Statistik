<?php

if (!isset($_GET['id']) || !Check::isIn($_GET['id'], 'events')) throw new PageNotFound();

$_id = $_GET['id'];


$event = $db->getFirstRow("
    SELECT *
    FROM `events`
    WHERE `id` = '".$db->escape($_id)."'
    LIMIT 1;");

$id = $event['id'];

echo '<h1>',htmlspecialchars($event['name']),'</h1>';
Title::set(htmlspecialchars($event['name']));


TempDB::generate('x_scores_male');
TempDB::generate('x_scores_female');
TempDB::generate('x_full_competitions');
$competitions = $db->getRows("
    SELECT *
    FROM `x_full_competitions`
    WHERE `event_id` = '".$id."'
    ORDER BY `date` DESC;
");

echo    '
    <table class="datatable">
    <thead>
      <tr>
        <th>Datum</th>
        <th>Ort</th>
        <th>Mann.</th>
        <th>HBw</th>
        <th>HBm</th>
        <th>GS</th>
        <th>LAw</th>
        <th>LAm</th>
        <th>FSw</th>
        <th>FSm</th>
        <th>HL</th>
        <th></th>
      </tr>
    </thead>
    <tbody>';


foreach ($competitions as $competition) {

    $hbm = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_male`
        WHERE `competition_id` = '".$competition['id']."'
        AND `discipline` = 'HB'
    ", 'count');
    $hbf = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_female`
        WHERE `competition_id` = '".$competition['id']."'
        AND `discipline` = 'HB'
    ", 'count');
    $gs = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_gs`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $laf = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_la`
        WHERE `competition_id` = '".$competition['id']."'
        AND `sex` = 'female'
    ", 'count');
    $lam = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_la`
        WHERE `competition_id` = '".$competition['id']."'
        AND `sex` = 'male'
    ", 'count');
    $fsf = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_fs`
        WHERE `competition_id` = '".$competition['id']."'
        AND `sex` = 'female'
    ", 'count');
    $fsm = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_fs`
        WHERE `competition_id` = '".$competition['id']."'
        AND `sex` = 'male'
    ", 'count');
    $hl = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_male`
        WHERE `competition_id` = '".$competition['id']."'
        AND `discipline` = 'HL'
    ", 'count');

    echo
        '<tr><td>',
          $competition['date'],
        '</td><td>',
            Link::place($competition['place_id'], $competition['place']),
        '</td><td>';

    if ($competition['score_type']) {
        echo $competition['persons'],'/',$competition['run'],'/',$competition['score'];
    }

    echo
        '</td><td>',
            $hbf,
        '</td><td>',
            $hbm,
        '</td><td>',
            $gs,
        '</td><td title="'.FSS::laType($competition['la']).'">',
            $laf,
        '</td><td title="'.FSS::laType($competition['la']).'">',
            $lam,
        '</td><td title="'.FSS::fsType($competition['fs']).'">',
            $fsf,
        '</td><td title="'.FSS::fsType($competition['fs']).'">',
            $fsm,
        '</td><td>',
            $hl,
        '</td><td>',
            Link::competition($competition['id']),
        '</td></tr>';
}

echo '</tbody></table>
<h2>Diagramme</h2>';


echo '<img src="chart.php?type=event&amp;discipline=HB&amp;sex=female&amp;id='.$id.'" alt=""/>';
echo '<img src="chart.php?type=event&amp;discipline=HL&amp;sex=male&amp;id='.$id.'" alt=""/>';
echo '<img src="chart.php?type=event&amp;discipline=HB&amp;sex=male&amp;id='.$id.'" alt=""/>';
