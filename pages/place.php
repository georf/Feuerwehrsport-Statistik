<?php

if (!isset($_GET['id']) || !Check::isIn($_GET['id'], 'places')) throw new PageNotFound();


TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');
TempDB::generate('x_full_competitions');




$_id = $_GET['id'];
$individual = 0;
$total_hbf = 0;
$total_hbm = 0;
$total_hl = 0;
$total_laf = 0;
$total_lam = 0;
$total_fsf = 0;
$total_fsm = 0;
$total_gs = 0;

$rows = $db->getRows("
    SELECT YEAR(`date`) AS `year`
    FROM `competitions`
    WHERE `place_id` = '".$db->escape($_id)."'
    GROUP BY `year`
    ORDER BY `year`
");
$years = array();
foreach ($rows as $y) {
    $years[] = $y['year'];
}


$place = $db->getFirstRow("
    SELECT *
    FROM `places`
    WHERE `id` = '".$db->escape($_id)."'
    LIMIT 1;");

$id = $place['id'];

Title::set(htmlspecialchars($place['name']));
echo '<h1>',htmlspecialchars($place['name']),'</h1>';

$competitions = $db->getRows("
    SELECT *
    FROM `x_full_competitions`
    WHERE `place_id` = '".$id."'
    ORDER BY `date` DESC;
");

echo    '
    <table class="datatable">
    <thead>
      <tr>
        <th>Datum</th>
        <th>Typ</th>
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
        FROM `x_scores_hbm`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $total_hbm += $hbm;

    $hbf = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_hbf`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $total_hbf += $hbf;

    $gs = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_gs`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $total_gs += $gs;

    $laf = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_la`
        WHERE `competition_id` = '".$competition['id']."'
        AND `sex` = 'female'
    ", 'count');
    $total_laf += $laf;

    $lam = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_la`
        WHERE `competition_id` = '".$competition['id']."'
        AND `sex` = 'male'
    ", 'count');
    $total_lam += $lam;

    $fsf = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_fs`
        WHERE `competition_id` = '".$competition['id']."'
        AND `sex` = 'female'
    ", 'count');
    $total_fsf += $fsf;

    $fsm = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_fs`
        WHERE `competition_id` = '".$competition['id']."'
        AND `sex` = 'male'
    ", 'count');
    $total_fsm += $fsm;

    $hl = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `x_scores_hl`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $total_hl += $hl;

    $individual += $hl + $hbm + $hbf;
    echo
        '<tr><td>',
          $competition['date'],
        '</td><td>',
            Link::event($competition['event_id'], $competition['event']),
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

<h2>Auswertung</h2>
<div class="row">
    <div class="five columns">
        <h4>Verteilung der Wettkämpfe über das Jahr</h4>
        <img src="chart.php?type=overview_month&amp;place='.$_id.'" alt="" class="big"/>
    </div>
    <div class="five columns">
        <h4>Verteilung der Wettkämpfe über die Woche</h4>
        <img src="chart.php?type=overview_week&amp;place='.$_id.'" alt="" class="big"/>
    </div>
    <div class="five columns">
        <h4>Angebotene Disziplinen pro Wettkampf</h4>
        <img src="chart.php?type=competitions_score_types&amp;place='.$_id.'" alt="" class="big"/>
    </div>
</div>
<div class="row">
    <div class="five columns">
        <h4>Anzahl der Mannschaften pro Wettkampf</h4>
        <img src="chart.php?type=competitions_team_counts&amp;place='.$_id.'" alt="" class="big"/>
    </div>';

if ($individual > 0) {
    echo '
    <div class="five columns">
        <h4>Mannschaftswertungen der Einzeldisziplinen</h4>
        <img src="chart.php?type=competitions_team_scores&amp;place='.$_id.'" alt="" class="big"/>
    </div>
    <div class="five columns">
        <h4>Anzahl der Einzelstarter pro Wettkampf</h4>
        <img src="chart.php?type=competitions_person_counts&amp;place='.$_id.'" alt="" class="big"/>
    </div>';
}
echo '
</div>

<h2>Disziplinen</h2>';

$disciplines = array(
    'hbf' => $total_hbf,
    'hbm' => $total_hbm,
    'hl' => $total_hl,
);

foreach ($disciplines as $d => $total) {
    switch ($d) {
        case 'hbf':
            $sex = 'female';
            $dis = 'hb';
            break;
        case 'hbm':
            $sex = 'male';
            $dis = 'hb';
            break;
        default:
        case 'hl':
            $sex = 'male';
            $dis = 'hl';
            break;
    }

    if ($total > 0) {

        $avg = $db->getFirstRow("
            SELECT AVG(`s`.`time`) AS `avg`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
            WHERE `s`.`discipline` = '".$db->escape($dis)."'
            AND `p`.`sex` = '".$sex."'
            AND `c`.`place_id` = '".$db->escape($_id)."'
            AND `time` IS NOT NULL
        ", 'avg');

        $best = $db->getFirstRow("
            SELECT `s`.`time`, `p`.`id`, `s`.`competition_id`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
            WHERE `s`.`discipline` = '".$db->escape($dis)."'
            AND `p`.`sex` = '".$sex."'
            AND `s`.`time` IS NOT NULL
            AND `c`.`place_id` = '".$db->escape($_id)."'
            ORDER BY `s`.`time`
        ");
        $c = FSS::competition($best['competition_id']);


        echo '
<div class="row hideshow" style="border:1px solid #BAE0F1">
    <div class="eleven columns headline">
        <h3>'.FSS::dis2img($dis).' '.FSS::dis2name($dis).' - '.FSS::sex($sex).'</h3>
    </div>
    <div class="five columns">
        <table>
            <tr><th>Durchschnitt</th><td>',FSS::time($avg),' s</td></tr>
            <tr><th>Anzahl</th><td>',$total,'</td></tr>
            <tr><th>Bestzeit</th><td>',FSS::time($best['time']),' s<br/>',Link::person($best['id'], 'full'),'<br/>',Link::competition($c['id'], gDate($c['date'])).' '.Link::event($c['event_id'], $c['event']).'</td></tr>
        </table>
    </div>
    <div class="five columns">
';


        echo '<table>';

        foreach ($years as $year) {
            $best = $db->getFirstRow("
                SELECT `s`.`time`, `p`.`id`, `s`.`competition_id`, `date`
                FROM `persons` `p`
                INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
                INNER JOIN `competitions` `c` ON `s`.`competition_id` = `c`.`id`
                WHERE `s`.`discipline` = '".$db->escape($dis)."'
                AND `p`.`sex` = '".$sex."'
                AND YEAR(`c`.`date`) = ".$year."
                AND `s`.`time` IS NOT NULL
                AND `c`.`place_id` = '".$db->escape($_id)."'
                ORDER BY `s`.`time`
            ");

            if ($best) {
                echo '<tr><th>'.$year.'</th><td>',FSS::time($best['time']),' s</td><td>',Link::person($best['id'], 'full'),'<br/>',Link::competition($best['competition_id'], gDate($best['date'])),'</td></tr>';
            }
        }


        echo    '</table></div></div>';
    }

}

$disciplines = array(
    'female' => $total_fsf,
    'male' => $total_fsm,
);

foreach ($disciplines as $sex => $total) {
    if ($total > 0) {


        echo '
        <div class="row hideshow" style="border:1px solid #BAE0F1">
            <div class="eleven columns headline">
                <h3>'.FSS::dis2img('fs').' Feuerwehrstafette - '.FSS::sex($sex).'</h3>
            </div>';

        $types = array(
            'Feuer' => ' = \'feuer\'',
            'Abstellen' => ' = \'abstellen\'',
            'unbekannt' => ' IS NULL',
        );

        foreach ($types as $t_name => $t_type) {
            $count = $db->getFirstRow("
                SELECT COUNT(`s`.`id`) AS `count`
                FROM `scores_fs` `s`
                INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                WHERE `s`.`sex` = '".$sex."'
                AND `c`.`place_id` = '".$db->escape($_id)."'
                AND `c`.`fs` ".$t_type."
            ", 'count');

            if ($count <= 0) continue;

            $avg = $db->getFirstRow("
                SELECT AVG(`s`.`time`) AS `avg`
                FROM `scores_fs` `s`
                INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                WHERE `s`.`sex` = '".$sex."'
                AND `c`.`place_id` = '".$db->escape($_id)."'
                AND `c`.`fs` ".$t_type."
                AND `time` IS NOT NULL
            ", 'avg');

            $best = $db->getFirstRow("
                SELECT `s`.*
                FROM `scores_fs` `s`
                INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                WHERE `s`.`sex` = '".$sex."'
                AND `c`.`place_id` = '".$db->escape($_id)."'
                AND `c`.`fs` ".$t_type."
                AND `time` IS NOT NULL
                ORDER BY `s`.`time`
            ");
            $c = FSS::competition($best['competition_id']);


            echo '

        <div class="six columns">
            <h4>Typ: ',$t_name,'</h4>
            <img src="styling/images/fs-'.strtolower($t_name).'.png" alt=""/>
        </div>
        <div class="six columns">
            <table>
                <tr><th colspan="2">Durchschnitt</th><td>',FSS::time($avg),' s</td></tr>
                <tr><th colspan="2">Anzahl</th><td>',$count,'</td></tr>
                <tr><th colspan="2">Bestzeit</th><td>',FSS::time($best['time']),' s<br/>',Link::team($best['team_id']),'<br/>',Link::competition($c['id'], gDate($c['date'])).' '.Link::event($c['event_id'], $c['event']).'</td></tr>';

        foreach ($years as $year) {
            $best = $db->getFirstRow("
                SELECT `s`.*,`c`.`date`
                FROM `scores_fs` `s`
                INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                WHERE `s`.`sex` = '".$sex."'
                AND `c`.`place_id` = '".$db->escape($_id)."'
                AND `c`.`fs` ".$t_type."
                AND `time` IS NOT NULL
                AND YEAR(`c`.`date`) = ".$year."
                ORDER BY `s`.`time`
            ");

            if ($best) {
                echo '<tr><th>'.$year.'</th><td>',FSS::time($best['time']),' s</td><td>',Link::team($best['team_id']),'<br/>',Link::competition($best['competition_id'], gDate($best['date'])),'</td></tr>';
            }
        }


        echo '</table>';

        echo '</div>';
        }

        echo '</div>';
    }
}

if ($total_gs > 0) {
    echo '
    <div class="row hideshow" style="border:1px solid #BAE0F1">
        <div class="eleven columns headline">
            <h3>'.FSS::dis2img('gs').' Gruppenstafette</h3>
        </div>';

    $avg = $db->getFirstRow("
        SELECT AVG(`s`.`time`) AS `avg`
        FROM `scores_gs` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        AND `c`.`place_id` = '".$db->escape($_id)."'
        AND `time` IS NOT NULL
    ", 'avg');

    $best = $db->getFirstRow("
        SELECT `s`.*
        FROM `scores_gs` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        AND `c`.`place_id` = '".$db->escape($_id)."'
        AND `time` IS NOT NULL
        ORDER BY `s`.`time`
    ");
    $c = FSS::competition($best['competition_id']);


    echo '
<div class="six columns">
    <table>
        <tr><th colspan="2">Durchschnitt</th><td>',FSS::time($avg),' s</td></tr>
        <tr><th colspan="2">Anzahl</th><td>',$total_gs,'</td></tr>
        <tr><th colspan="2">Bestzeit</th><td>',FSS::time($best['time']),' s<br/>',Link::team($best['team_id']),'<br/>',Link::competition($c['id'], gDate($c['date'])).' '.Link::event($c['event_id'], $c['event']).'</td></tr>';

    foreach ($years as $year) {
        $best = $db->getFirstRow("
            SELECT `s`.*,`c`.`date`
            FROM `scores_gs` `s`
            INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
            AND `c`.`place_id` = '".$db->escape($_id)."'
            AND `time` IS NOT NULL
            AND YEAR(`c`.`date`) = ".$year."
            ORDER BY `s`.`time`
        ");

        if ($best) {
            echo '<tr><th>'.$year.'</th><td>',FSS::time($best['time']),' s</td><td>',Link::team($best['team_id']),'<br/>',Link::competition($best['competition_id'], gDate($best['date'])),'</td></tr>';
        }
    }


    echo '</table>';

    echo '</div>';
    echo '</div>';
}

