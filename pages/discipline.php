<?php

if (!isset($_GET['id']) || !in_array($_GET['id'], array('hl','hb'))) throw new PageNotFound();
$_dis = $_GET['id'];


$rows = $db->getRows("
    SELECT YEAR(`date`) AS `year`
    FROM `competitions`
    GROUP BY `year`
    ORDER BY `year`
");
$years = array();
foreach ($rows as $y) {
    $years[] = $y['year'];
}

echo '<h1>',FSS::dis2name($_dis),'</h1>';

echo '<h2>Allgemeine Statistiken</h2>';
echo '<table class="table">';

echo '<tr><td></td><th>Gesamt</th><th>weiblich</th><th>männlich</th></tr>';
echo '<tr><th>Wettkämpfe</th><td>',$db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `c`.`id`
        FROM `competitions` `c`
        INNER JOIN `scores` `s` ON `s`.`competition_id` = `c`.`id`
        WHERE `s`.`discipline` = '".$db->escape($_dis)."'
        GROUP BY `c`.`id`
    ) `i`
", 'count'),'</td><td></td><td></td></tr>';

TempDB::generate('x_scores_female');
$female = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `id`
        FROM `x_scores_female`
        WHERE `discipline` = '".$db->escape($_dis)."'
        GROUP BY `person_id`
    ) `i`
", 'count');

TempDB::generate('x_scores_male');
$male = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `id`
        FROM `x_scores_male`
        WHERE `discipline` = '".$db->escape($_dis)."'
        GROUP BY `person_id`
    ) `i`
", 'count');
echo '<tr><th>Wettkämpfer</th><td>',($female + $male),'</td><td>',$female,'</td><td>',$male,'</td></tr>';


$femaleA = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `p`.`id`
        FROM `persons` `p`
        INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
        WHERE `s`.`discipline` = '".$db->escape($_dis)."'
        AND `p`.`sex` = 'female'
    ) `i`
", 'count');
$maleA = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `p`.`id`
        FROM `persons` `p`
        INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
        WHERE `s`.`discipline` = '".$db->escape($_dis)."'
        AND `p`.`sex` = 'male'
    ) `i`
", 'count');
echo '<tr><th>Zeiten</th><td>',($femaleA + $maleA),'</td><td>',$femaleA,'</td><td>',$maleA,'</td></tr>';


$female = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `p`.`id`
        FROM `persons` `p`
        INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
        WHERE `s`.`discipline` = '".$db->escape($_dis)."'
        AND `s`.`time` IS NULL
        AND `p`.`sex` = 'female'
    ) `i`
", 'count');
$male = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `p`.`id`
        FROM `persons` `p`
        INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
        WHERE `s`.`discipline` = '".$db->escape($_dis)."'
        AND `s`.`time` IS NULL
        AND `p`.`sex` = 'male'
    ) `i`
", 'count');
echo '<tr><th>Ungültige</th>',
    '<td>',($female + $male),' ('.round(($female + $male) / ($femaleA + $maleA) * 100, 1).' %)</td>',
    '<td>',$female,' ('.round($female / $femaleA * 100, 1).' %)</td>',
    '<td>',$male,' ('.round($male / $maleA * 100, 1).' %)</td>',
    '</tr>';


$all = $db->getFirstRow("
    SELECT AVG(`time`) AS `avg`
    FROM `scores`
    WHERE `discipline` = '".$db->escape($_dis)."'
    AND `time` IS NOT NULL
", 'avg');
$female = $db->getFirstRow("
    SELECT AVG(`s`.`time`) AS `avg`
    FROM `persons` `p`
    INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
    WHERE `s`.`discipline` = '".$db->escape($_dis)."'
    AND `p`.`sex` = 'female'
    AND `time` IS NOT NULL
", 'avg');
$male = $db->getFirstRow("
    SELECT AVG(`s`.`time`) AS `avg`
    FROM `persons` `p`
    INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
    WHERE `s`.`discipline` = '".$db->escape($_dis)."'
    AND `p`.`sex` = 'male'
    AND `time` IS NOT NULL
", 'avg');
echo '<tr><th>Durchschnitt</th><td>',FSS::time($all),' s</td><td>',FSS::time($female),' s</td><td>',FSS::time($male),' s</td></tr>';


$bestf = $db->getFirstRow("
    SELECT `s`.`time`, `p`.`id`, `s`.`competition_id`
    FROM `persons` `p`
    INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
    WHERE `s`.`discipline` = '".$db->escape($_dis)."'
    AND `p`.`sex` = 'female'
    AND `s`.`time` IS NOT NULL
    ORDER BY `s`.`time`
");
$bestm = $db->getFirstRow("
    SELECT `s`.`time`, `p`.`id`, `s`.`competition_id`
    FROM `persons` `p`
    INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
    WHERE `s`.`discipline` = '".$db->escape($_dis)."'
    AND `p`.`sex` = 'male'
    AND `s`.`time` IS NOT NULL
    ORDER BY `s`.`time`
");
echo '<tr><th>Bestzeit</th><td></td><td>',FSS::time($bestf['time']),' s</td><td>',FSS::time($bestm['time']),' s</td></tr>';

echo '</table>';

echo '<img src="chart.php?type=discipline_overview&key='.$_dis.'" class="big" alt=""/>';



echo '<h2>Bestzeiten und Teilnahmen</h2>';
echo '<table class="table">';

$cf = FSS::competition($bestf['competition_id']);
$cm = FSS::competition($bestm['competition_id']);
echo '<tr><td></td><th colspan="2">weiblich</th><th colspan="2">männlich</th></tr>';
echo '<tr><th>Bestzeit</th>',
        '<td>',FSS::time($bestf['time']),' s</td><td>',Link::person($bestf['id'], 'full'),'<br/>',Link::competition($cf['id'], gDate($cf['date']). ' '.$cf['place']),'<br/>'.Link::event($cf['event_id'], $cf['event']).'</td>',
        '<td>',FSS::time($bestm['time']),' s</td><td>',Link::person($bestm['id'], 'full'),'<br/>',Link::competition($cm['id'], gDate($cm['date']). ' '.$cm['place']),'<br/>'.Link::event($cm['event_id'], $cm['event']).'</td>',
    '</tr>',
    '<tr><th colspan="5" style="text-align:center">Nach Wettkampf-Typ</th></tr>';


foreach (array(1 => 'D-Cup', 5 => 'Deutsche Meisterschaft', 12 => 'CTIF', 11 => 'Weltmeisterschaft') as $id => $name) {

    echo '<tr><th>Bestzeit<br/>'.Link::event($id, $name).'</th>';

    foreach (array('female', 'male') as $s) {
        $best = $db->getFirstRow("
            SELECT `s`.`time`, `p`.`id`, `s`.`competition_id`, `date`, `place_id`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            INNER JOIN `competitions` `c` ON `s`.`competition_id` = `c`.`id`
            WHERE `s`.`discipline` = '".$db->escape($_dis)."'
            AND `p`.`sex` = '".$s."'
            AND `c`.`event_id` = ".$id."
            AND `s`.`time` IS NOT NULL
            ORDER BY `s`.`time`
        ");
        if ($best) {
            echo '<td>',FSS::time($best['time']),' s</td><td>',Link::person($best['id'], 'full'),'<br/>',Link::competition($best['competition_id'], gDate($best['date'])),'<br/>'.Link::place($best['place_id']).'</td>';
        } else {
            echo '<td></td><td></td>';
        }
    }
    echo '</tr>';
}

echo '<tr><th colspan="5" style="text-align:center">Nach Jahr</th></tr>';

foreach ($years as $year) {

    echo '<tr><th>Bestzeit<br/>'.$year.'</th>';

    foreach (array('female', 'male') as $s) {
        $best = $db->getFirstRow("
            SELECT `s`.`time`, `p`.`id`, `s`.`competition_id`, `date`, `place_id`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            INNER JOIN `competitions` `c` ON `s`.`competition_id` = `c`.`id`
            WHERE `s`.`discipline` = '".$db->escape($_dis)."'
            AND `p`.`sex` = '".$s."'
            AND YEAR(`c`.`date`) = ".$year."
            AND `s`.`time` IS NOT NULL
            ORDER BY `s`.`time`
        ");
        if ($best) {
            echo '<td>',FSS::time($best['time']),' s</td><td>',Link::person($best['id'], 'full'),'<br/>',Link::competition($best['competition_id'], gDate($best['date'])),'<br/>'.Link::place($best['place_id']).'</td>';
        } else {
            echo '<td></td><td></td>';
        }
    }
    echo '</tr>';
}


echo '<tr><th colspan="5" style="text-align:center">Höchste Anzahl der Zeiten bei Wettkampf</th></tr>';

echo '<tr><th>Anzahl Zeiten</th>';


    foreach (array('female', 'male') as $s) {
    $max = $db->getRows("
        SELECT `p`.`id`, COUNT(`s`.`id`) AS `count`
        FROM `persons` `p`
        INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
        WHERE `s`.`discipline` = '".$db->escape($_dis)."'
        AND `p`.`sex` = '".$s."'
        GROUP BY `p`.`id`
        ORDER BY `count` DESC
    ");
    $c = $max[0]['count'];
    $a = array();
    echo '<td>',$c,'</td><td>';

    foreach ($max as $m) {
        if ($m['count'] == $c) {
            $a[] = Link::person($m['id'], 'sub');
        }
    }
    echo implode(', ', $a),'</td>';
}

echo '</tr>';



foreach (array(1 => 'D-Cup', 5 => 'Deutsche Meisterschaft', 12 => 'CTIF', 11 => 'Weltmeisterschaft') as $id => $name) {

    echo '<tr><th>Anzahl Zeiten<br/>'.Link::event($id, $name).'</th>';

    foreach (array('female', 'male') as $s) {
        $max = $db->getRows("
            SELECT `p`.`id`, COUNT(`s`.`id`) AS `count`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            INNER JOIN `competitions` `c` ON `s`.`competition_id` = `c`.`id`
            WHERE `s`.`discipline` = '".$db->escape($_dis)."'
            AND `p`.`sex` = '".$s."'
            AND `c`.`event_id` = ".$id."
            GROUP BY `p`.`id`
            ORDER BY `count` DESC
        ");
        if (count($max)) {
            $c = $max[0]['count'];
            $a = array();
            echo '<td>',$c,'</td><td>';

            foreach ($max as $m) {
                if ($m['count'] == $c) {
                    $a[] = Link::person($m['id'], 'sub');
                }
            }
            echo implode(', ', $a),'</td>';
        } else {
            echo '<td></td><td></td>';
        }
    }
    echo '</tr>';
}
echo '</table>';

echo '<h2>Jahresbezogene Statistiken</h2>';
