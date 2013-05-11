<?php

$sexConfig = array(
    'female' => 'weiblich',
    'male' => 'männlich',
);

if (!isset($_GET['year'])
|| !is_numeric($_GET['year'])
|| intval($_GET['year']) < 1990
|| intval($_GET['year']) > intval(date('Y'))) throw new PageNotFound();


$_year = $_GET['year'];

// generate scores_year
$db->query("
    CREATE TEMPORARY TABLE `scores_year`
    SELECT `s`.*
    FROM `scores` `s`
    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
    WHERE YEAR(`c`.`date`) = '".$db->escape($_year)."'
");

// generate hl
$db->query("
    CREATE TEMPORARY TABLE `hl`
    SELECT `s`.*,`p`.`name`,`p`.`firstname`
    FROM `scores_year` `s`
    INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
    WHERE `discipline` = 'HL'
");
echo mysql_error();

// generate hb
$db->query("
    CREATE TEMPORARY TABLE `hb_female`
    SELECT `s`.*,`p`.`name`,`p`.`firstname`
    FROM `scores_year` `s`
    INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
    WHERE `discipline` = 'HB'
    AND `p`.`sex` = 'female'
");
$db->query("
    CREATE TEMPORARY TABLE `hb_male`
    SELECT `s`.*,`p`.`name`,`p`.`firstname`
    FROM `scores_year` `s`
    INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
    WHERE `discipline` = 'HB'
    AND `p`.`sex` = 'male'
");


$hb[$sex] = $db->getRows("
    SELECT `best`.*,
        `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
        `p`.`name` AS `name`,`p`.`firstname` AS `firstname`
    FROM (
        SELECT *
        FROM (
            (
                SELECT `id`,`team_id`,`team_number`,
                `person_id`,
                `time`
                FROM `hb_female`
                WHERE `time` IS NOT NULL
                AND `team_number` != -2
            ) UNION (
                SELECT `id`,`team_id`,`team_number`,
                `person_id`,
                ".FSS::INVALID." AS `time`
                FROM `hb_female`
                WHERE `time` IS NULL
                AND `competition_id` = '".$id."'
                AND `team_number` != -2
            ) ORDER BY `time`
        ) `all`
        GROUP BY `person_id`
    ) `best`
    LEFT JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
    INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
    WHERE `sex` = '".$sex."'
    ORDER BY `time`
");



?>
<ul class="disc">
<li>Bester Sportler des Jahres?</li>
<li>Bester Sportler nach Disziplin?</li>
<li>HL, HB, ZK</li>
<li>Beste Löschangriff-Mannschaft nach Typ</li>
<li>Bester Staffellauf nach Typ</li>
<li>Starter mit meisten Teilnahmen</li>
<li>Durchschnitt des Jahres?</li>
<li>Mannschaftswertung</li>
</ul>
