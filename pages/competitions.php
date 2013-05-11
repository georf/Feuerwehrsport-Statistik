<?php
Title::set('Wettkämpfe');

TempDB::generate('x_scores_male');
TempDB::generate('x_scores_female');
TempDB::generate('x_full_competitions');



echo '
    <h1>Wettkämpfe</h1>
      <table class="datatable">
        <thead>
          <tr>
            <th>Datum</th>
            <th>Typ</th>
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
            <th></th>
          </tr>
        </thead>
        <tbody>';

$competitions = $db->getRows("
    SELECT *
    FROM `x_full_competitions`
    ORDER BY `date` DESC;
");

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
        FROM `scores_gruppenstafette`
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
            Link::event($competition['event_id'], $competition['event']),
        '</td><td>',
            Link::place($competition['place_id'], $competition['place']),
        '</td><td>';

    if ($competition['score_type']) {
        echo $competition['persons'],'/',$competition['run'],'/',$competition['score'];
    }

    echo
        '</td><td>',
            ($hbf == 0)? '':$hbf,
        '</td><td>',
            ($hbm == 0)? '':$hbm,
        '</td><td>',
            ($gs == 0)? '':$gs,
        '</td><td title="'.FSS::laType($competition['la']).'">',
            ($laf == 0)? '':$laf,
        '</td><td title="'.FSS::laType($competition['la']).'">',
            ($lam == 0)? '':$lam,
        '</td><td title="'.FSS::fsType($competition['fs']).'">',
            ($fsf == 0)? '':$fsf,
        '</td><td title="'.FSS::fsType($competition['fs']).'">',
            ($fsm == 0)? '':$fsm,
        '</td><td>',
            ($hl == 0)? '':$hl,
        '</td><td>',
            Link::competition($competition['id'], 'ⓘ'),
        '</td><td style="'.getMissedColor($competition['missed']).'" title="'.getMissedTitle($competition['missed']).'"></td></tr>';
}
?>

</tbody></table>

<h2>Auswertung</h2>
<div class="row">
    <div class="five columns">
        <h4>Verteilung der Wettkämpfe über das Jahr</h4>
        <img src="chart.php?type=overview_month" alt="" class="big"/>
    </div>
    <div class="five columns">
        <h4>Verteilung der Wettkämpfe über die Woche</h4>
        <img src="chart.php?type=overview_week" alt="" class="big"/>
    </div>
</div>
