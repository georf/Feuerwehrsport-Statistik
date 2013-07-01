<?php

if (isset($_GET['delete']) && Check::isIn($_GET['delete'], 'persons')) {
    $db->deleteRow('persons', $_GET['delete']);

    header('Location: ?page=administration&admin=find_bad_persons');
    exit();
}


$persons = $db->getRows("
    SELECT *
    FROM `persons`
");

echo '<table class="table"><tr><th>Name</th><th>Vorname</th><th>sex</th><th>id</th></tr>';

foreach ($persons as $person) {
    $id = $person['id'];

    $hb = $db->getRows("
        SELECT
            `c`.`place_id`,`p`.`name` AS `place`,
            `c`.`event_id`,`e`.`name` AS `event`,
            `c`.`score_type_id`,
            `s`.`competition_id`,`c`.`date`,
            `s`.`time`,`s`.`team_id`,
            `s`.`id` AS `score_id`,`s`.`team_number`
        FROM `scores` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
        INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
        WHERE `person_id` = '".$id."'
        AND `discipline` = 'HB'
        ORDER BY `date` DESC
    ");

    $hl = $db->getRows("
        SELECT
            `c`.`place_id`,`p`.`name` AS `place`,
            `c`.`event_id`,`e`.`name` AS `event`,
            `c`.`score_type_id`,
            `s`.`competition_id`,`c`.`date`,
            `s`.`time`,`s`.`team_id`,
            `s`.`id` AS `score_id`,`s`.`team_number`
        FROM `scores` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
        INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
        WHERE `person_id` = '".$id."'
        AND `discipline` = 'HL'
        ORDER BY `date` DESC
    ");


    $gs = $db->getRows("
        SELECT
            `c`.`place_id`,`p`.`name` AS `place`,
            `c`.`event_id`,`e`.`name` AS `event`,
            `c`.`score_type_id`,
            `s`.`competition_id`,`c`.`date`,
            `s`.`time`,`s`.`team_id`,
            `s`.`id` AS `score_id`,`s`.`team_number`,
            `s`.`person_1`,`s`.`person_2`,`s`.`person_3`,`s`.`person_4`,`s`.`person_5`,`s`.`person_6`
        FROM `scores_gs` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
        INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
        WHERE `person_1` = '".$id."'
        OR `person_2` = '".$id."'
        OR `person_3` = '".$id."'
        OR `person_4` = '".$id."'
        OR `person_5` = '".$id."'
        OR `person_6` = '".$id."'
        ORDER BY `date` DESC
    ");

    $la = $db->getRows("
        SELECT
            `c`.`place_id`,`p`.`name` AS `place`,
            `c`.`event_id`,`e`.`name` AS `event`,
            `c`.`score_type_id`,
            `s`.`competition_id`,`c`.`date`,
            `s`.`time`,`s`.`team_id`,
            `s`.`id` AS `score_id`,`s`.`team_number`,
            `s`.`person_1`,`s`.`person_2`,`s`.`person_3`,`s`.`person_4`,`s`.`person_5`,`s`.`person_6`,`s`.`person_7`
        FROM `scores_la` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
        INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
        WHERE `person_1` = '".$id."'
        OR `person_2` = '".$id."'
        OR `person_3` = '".$id."'
        OR `person_4` = '".$id."'
        OR `person_5` = '".$id."'
        OR `person_6` = '".$id."'
        OR `person_7` = '".$id."'
        ORDER BY `date` DESC
    ");

    $fs = $db->getRows("
        SELECT
            `c`.`place_id`,`p`.`name` AS `place`,
            `c`.`event_id`,`e`.`name` AS `event`,
            `c`.`score_type_id`,
            `s`.`competition_id`,`c`.`date`,
            `s`.`time`,`s`.`team_id`,
            `s`.`id` AS `score_id`,`s`.`team_number`,
            `s`.`person_1`,`s`.`person_2`,`s`.`person_3`,`s`.`person_4`
        FROM `scores_fs` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
        INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
        WHERE `person_1` = '".$id."'
        OR `person_2` = '".$id."'
        OR `person_3` = '".$id."'
        OR `person_4` = '".$id."'
        ORDER BY `date` DESC
    ");

    if (
        !count($hl) &&
        !count($hb) &&
        !count($gs) &&
        !count($la) &&
        !count($fs)
    ) {
        echo '<tr><td>'.$person['name'].'</td><td>'.$person['firstname'].'</td><td>'.$person['sex'].'</td><td><a href="?page=administration&amp;admin=find_bad_persons&amp;delete='.$person['id'].'">'.$person['id'].'</a></td></tr>';
    }
}

echo '</table>';
