<?php

if (!isset($_GET['id']) || !in_array($_GET['id'], array('hl','hb'))) throw new PageNotFound();
$_dis = $_GET['id'];

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


$female = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `p`.`id`
        FROM `persons` `p`
        INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
        WHERE `s`.`discipline` = '".$db->escape($_dis)."'
        AND `p`.`sex` = 'female'
        GROUP BY `p`.`id`
    ) `i`
", 'count');
$male = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
        SELECT `p`.`id`
        FROM `persons` `p`
        INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
        WHERE `s`.`discipline` = '".$db->escape($_dis)."'
        AND `p`.`sex` = 'male'
        GROUP BY `p`.`id`
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
echo '<tr><th>Ungültige</th><td>',($female + $male),'</td><td>',$female,'</td><td>',$male,'</td></tr>';
echo '</table>';
