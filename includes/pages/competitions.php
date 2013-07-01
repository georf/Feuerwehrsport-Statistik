<?php
Title::set('Wettkämpfe');

TempDB::generate('x_scores_male');
TempDB::generate('x_scores_female');
TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');

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
    ORDER BY `date` DESC
");

foreach ($competitions as $competition) {

    $hbm = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_hbm`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $hbf = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_hbf`
        WHERE `competition_id` = '".$competition['id']."'
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
        FROM `x_scores_hl`
        WHERE `competition_id` = '".$competition['id']."'
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
            FSS::countNoEmpty($hbf),
        '</td><td>',
            FSS::countNoEmpty($hbm),
        '</td><td>',
            FSS::countNoEmpty($gs),
        '</td><td title="'.FSS::laType($competition['la']).'">',
            FSS::countNoEmpty($laf),
        '</td><td title="'.FSS::laType($competition['la']).'">',
            FSS::countNoEmpty($lam),
        '</td><td title="'.FSS::fsType($competition['fs']).'">',
            FSS::countNoEmpty($fsf),
        '</td><td title="'.FSS::fsType($competition['fs']).'">',
            FSS::countNoEmpty($fsm),
        '</td><td>',
            FSS::countNoEmpty($hl),
        '</td><td>',
            Link::competition($competition['id'], 'Info'),
        '</td><td style="'.getMissedColor($competition['missed']).'" title="'.getMissedTitle($competition['missed']).'"></td></tr>';
}
?>

</tbody></table>

<h2>Auswertung</h2>
<div class="row">
    <div class="five columns">
        <h4>Verteilung der Wettkämpfe über das Jahr</h4><?=Chart::img('overview_month')?>
    </div>
    <div class="five columns">
        <h4>Verteilung der Wettkämpfe über die Woche</h4><?=Chart::img('overview_week')?>
    </div>
    <div class="five columns">
        <h4>Angebotene Disziplinen pro Wettkampf</h4><?=Chart::img('competitions_score_types')?>
    </div>
</div>
<div class="row">
    <div class="five columns">
        <h4>Mannschaftswertungen der Einzeldisziplinen</h4><?=Chart::img('competitions_team_scores')?>
    </div>
    <div class="five columns">
        <h4>Anzahl der Mannschaften pro Wettkampf</h4><?=Chart::img('competitions_team_counts')?>
    </div>
    <div class="five columns">
        <h4>Anzahl der Einzelstarter pro Wettkampf</h4><?=Chart::img('competitions_person_counts')?>
    </div>
</div>
