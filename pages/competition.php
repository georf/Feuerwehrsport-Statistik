<?php

if (!isset($_GET['id']) || !Check::isIn($_GET['id'], 'competitions')) throw new PageNotFound();

$_id = $_GET['id'];


if (isset($_SESSION['loggedin'])) {

    $competition = $db->getFirstRow("
        SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`,
            `t`.`persons`,`t`.`run`,`t`.`score`,`t`.`id` AS `score_type`
        FROM `competitions` `c`
        INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
        INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
        LEFT JOIN `score_types` `t` ON `t`.`id` = `c`.`score_type_id`
        WHERE `c`.`id` = '".$db->escape($_id)."'
        LIMIT 1;
    ");

    $id = $competition['id'];

    if (isset($_POST['la-type'])) {
        if (isset($config['la'][$_POST['la-type']])) {
            $db->updateRow('competitions', $id, array(
                'la' => $_POST['la-type']
            ));

            $competition['la'] = $_POST['la-type'];
        } else {
            $db->updateRow('competitions', $id, array(
                'la' => NULL
            ));

            $competition['la'] = NULL;
        }
    }


    if (isset($_POST['fs-type'])) {
        if (isset($config['fs'][$_POST['fs-type']])) {
            $db->updateRow('competitions', $id, array(
                'fs' => $_POST['fs-type']
            ));

            $competition['fs'] = $_POST['fs-type'];
        } else {
            $db->updateRow('competitions', $id, array(
                'fs' => NULL
            ));

            $competition['fs'] = NULL;
        }
    }




    $update = array();
    foreach ($config['missed'] as $key=>$value) {
        if (isset($_POST['missed-'.$key]) && $_POST['missed-'.$key] == 'true') {
            $update[] = $key;
        }
    }
    if (count($update)) {
        $db->updateRow('competitions', $id, array(
            'missed' => implode(',', $update)
        ));

        $competition['missed'] = implode(',', $update);
    }

    echo '<div class="row">';

    echo '<div class="six columns">';
    echo '<form method="post">';
    $m = explode(',',$competition['missed']);
    foreach ($config['missed'] as $key=>$value) {
        echo '<input type="checkbox" ';
        if (in_array($key, $m)) {
            echo ' checked="checked" ';
        }
        echo ' name="missed-'.$key.'" value="true" id="missed-'.$key.'"/><label for="missed-'.$key.'">'.$value.'</label><br/>';
    }
    echo '<button type="submit">Speichern</button></form>';
    echo '</div>';


    echo '<div class="six columns">';
    echo '<form method="post">';

    echo '<select name="la-type">';
    echo '<option value="" ';
    if (!$competition['la']) {
        echo ' selected="selected" ';
    }
    echo ' />Nicht gelaufen</option>';

    foreach ($config['la'] as $key=>$value) {
        echo '<option value="'.$key.'" ';
        if ($key == $competition['la']) {
            echo ' selected="selected" ';
        }
        echo ' />'.$value.'</option>';
    }
    echo '</select>';
    echo '<button type="submit">Speichern</button></form>';
    echo '</div>';


    echo '<div class="six columns">';
    echo '<form method="post">';

    echo '<select name="fs-type">';
    echo '<option value="" ';
    if (!$competition['fs']) {
        echo ' selected="selected" ';
    }
    echo ' />Nicht gelaufen</option>';

    foreach ($config['fs'] as $key=>$value) {
        echo '<option value="'.$key.'" ';
        if ($key == $competition['fs']) {
            echo ' selected="selected" ';
        }
        echo ' />'.$value.'</option>';
    }
    echo '</select>';
    echo '<button type="submit">Speichern</button></form>';
    echo '</div>';

    echo '<span class="bt user-group-new" id="add-score-type" title="Mannschaftswertung ändern">&nbsp;</span>';

    echo '</div>';
}


$cache = Cache::get();
if ($cache) {
    echo $cache;
} else {
    ob_start();

    $competition = $db->getFirstRow("
        SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`,
            `t`.`persons`,`t`.`run`,`t`.`score`,`t`.`id` AS `score_type`
        FROM `competitions` `c`
        INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
        INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
        LEFT JOIN `score_types` `t` ON `t`.`id` = `c`.`score_type_id`
        WHERE `c`.`id` = '".$db->escape($_id)."'
        LIMIT 1;
    ");

    $id = $competition['id'];

    $files = $db->getRows("
        SELECT *
        FROM `file_uploads`
        WHERE `competition_id` = '".$id."'
        ORDER BY `name`
    ");

    foreach ($files as $key => $file) {
        $files[$key]['content'] = explode(',', $file['content']);
    }

    echo dataDiv($competition, 'competition');




    $gs = $db->getRows("
        SELECT `best`.*,
            `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
            `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
            `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
            `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
            `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`,
            `p5`.`name` AS `name5`,`p5`.`firstname` AS `firstname5`,
            `p6`.`name` AS `name6`,`p6`.`firstname` AS `firstname6`
        FROM (
            SELECT *
            FROM (
                (
                    SELECT `id`,`team_id`,`team_number`,
                    `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,
                    `time`
                    FROM `scores_gruppenstafette`
                    WHERE `time` IS NOT NULL
                    AND `competition_id` = '".$id."'
                ) UNION (
                    SELECT `id`,`team_id`,`team_number`,
                    `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,
                    ".FSS::INVALID." AS `time`
                    FROM `scores_gruppenstafette`
                    WHERE `time` IS NULL
                    AND `competition_id` = '".$id."'
                ) ORDER BY `time`
            ) `all`
            GROUP BY `team_id`,`team_number`
        ) `best`

        INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
        LEFT JOIN `persons` `p1` ON `best`.`person_1` = `p1`.`id`
        LEFT JOIN `persons` `p2` ON `best`.`person_2` = `p2`.`id`
        LEFT JOIN `persons` `p3` ON `best`.`person_3` = `p3`.`id`
        LEFT JOIN `persons` `p4` ON `best`.`person_4` = `p4`.`id`
        LEFT JOIN `persons` `p5` ON `best`.`person_5` = `p5`.`id`
        LEFT JOIN `persons` `p6` ON `best`.`person_6` = `p6`.`id`
        ORDER BY `time`
    ");


    $la = array();
    $fs = array();
    $hb = array();
    $sexes = array('female', 'male');
    foreach ($sexes as $sex) {
        $la[$sex] = $db->getRows("
            SELECT `best`.*,
                `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
                `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
                `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
                `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
                `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`,
                `p5`.`name` AS `name5`,`p5`.`firstname` AS `firstname5`,
                `p6`.`name` AS `name6`,`p6`.`firstname` AS `firstname6`,
                `p7`.`name` AS `name7`,`p7`.`firstname` AS `firstname7`
            FROM (
                SELECT *
                FROM (
                    (
                        SELECT `id`,`team_id`,`team_number`,
                        `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`,
                        `time`
                        FROM `scores_loeschangriff`
                        WHERE `time` IS NOT NULL
                        AND `sex` = '".$sex."'
                        AND `competition_id` = '".$id."'
                    ) UNION (
                        SELECT `id`,`team_id`,`team_number`,
                        `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`,
                        ".FSS::INVALID." AS `time`
                        FROM `scores_loeschangriff`
                        WHERE `time` IS NULL
                        AND `sex` = '".$sex."'
                        AND `competition_id` = '".$id."'
                    ) ORDER BY `time`
                ) `all`
                GROUP BY `team_id`,`team_number`
            ) `best`

            INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
            LEFT JOIN `persons` `p1` ON `best`.`person_1` = `p1`.`id`
            LEFT JOIN `persons` `p2` ON `best`.`person_2` = `p2`.`id`
            LEFT JOIN `persons` `p3` ON `best`.`person_3` = `p3`.`id`
            LEFT JOIN `persons` `p4` ON `best`.`person_4` = `p4`.`id`
            LEFT JOIN `persons` `p5` ON `best`.`person_5` = `p5`.`id`
            LEFT JOIN `persons` `p6` ON `best`.`person_6` = `p6`.`id`
            LEFT JOIN `persons` `p7` ON `best`.`person_7` = `p7`.`id`
            ORDER BY `time`
        ");


        $fs[$sex] = $db->getRows("
            SELECT `best`.*,
                `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
                `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
                `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
                `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
                `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`
            FROM (
                SELECT *
                FROM (
                    (
                        SELECT `id`,`team_id`,`team_number`,
                        `person_1`,`person_2`,`person_3`,`person_4`,
                        `time`
                        FROM `scores_stafette`
                        WHERE `time` IS NOT NULL
                        AND `sex` = '".$sex."'
                        AND `competition_id` = '".$id."'
                    ) UNION (
                        SELECT `id`,`team_id`,`team_number`,
                        `person_1`,`person_2`,`person_3`,`person_4`,
                        ".FSS::INVALID." AS `time`
                        FROM `scores_stafette`
                        WHERE `time` IS NULL
                        AND `sex` = '".$sex."'
                        AND `competition_id` = '".$id."'
                    ) ORDER BY `time`
                ) `all`
                GROUP BY `team_id`,`team_number`
            ) `best`

            INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
            LEFT JOIN `persons` `p1` ON `best`.`person_1` = `p1`.`id`
            LEFT JOIN `persons` `p2` ON `best`.`person_2` = `p2`.`id`
            LEFT JOIN `persons` `p3` ON `best`.`person_3` = `p3`.`id`
            LEFT JOIN `persons` `p4` ON `best`.`person_4` = `p4`.`id`
            ORDER BY `time`
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
                        FROM `scores`
                        WHERE `time` IS NOT NULL
                        AND `competition_id` = '".$id."'
                        AND `discipline` = 'HB'
                        AND `team_number` != -2
                    ) UNION (
                        SELECT `id`,`team_id`,`team_number`,
                        `person_id`,
                        ".FSS::INVALID." AS `time`
                        FROM `scores`
                        WHERE `time` IS NULL
                        AND `competition_id` = '".$id."'
                        AND `discipline` = 'HB'
                        AND `team_number` != -2
                    ) ORDER BY `time`
                ) `all`
                GROUP BY `person_id`
            ) `best`
            INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
            INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
            WHERE `sex` = '".$sex."'
            ORDER BY `time`
        ");

        $hbFinale[$sex] = $db->getRows("
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
                        FROM `scores`
                        WHERE `time` IS NOT NULL
                        AND `competition_id` = '".$id."'
                        AND `discipline` = 'HB'
                        AND `team_number` = -2
                    ) UNION (
                        SELECT `id`,`team_id`,`team_number`,
                        `person_id`,
                        ".FSS::INVALID." AS `time`
                        FROM `scores`
                        WHERE `time` IS NULL
                        AND `competition_id` = '".$id."'
                        AND `discipline` = 'HB'
                        AND `team_number` = -2
                    ) ORDER BY `time`
                ) `all`
                GROUP BY `person_id`
            ) `best`
            INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
            INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
            WHERE `sex` = '".$sex."'
            ORDER BY `time`
        ");
    }

    $hl = $db->getRows("
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
                    FROM `scores`
                    WHERE `time` IS NOT NULL
                    AND `competition_id` = '".$id."'
                    AND `discipline` = 'HL'
                    AND `team_number` != -2
                ) UNION (
                    SELECT `id`,`team_id`,`team_number`,
                    `person_id`,
                    ".FSS::INVALID." AS `time`
                    FROM `scores`
                    WHERE `time` IS NULL
                    AND `competition_id` = '".$id."'
                    AND `discipline` = 'HL'
                    AND `team_number` != -2
                ) ORDER BY `time`
            ) `all`
            GROUP BY `person_id`
        ) `best`
        INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
        INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
        ORDER BY `time`
    ");

    $hlFinale = $db->getRows("
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
                    FROM `scores`
                    WHERE `time` IS NOT NULL
                    AND `competition_id` = '".$id."'
                    AND `discipline` = 'HL'
                    AND `team_number` = -2
                ) UNION (
                    SELECT `id`,`team_id`,`team_number`,
                    `person_id`,
                    ".FSS::INVALID." AS `time`
                    FROM `scores`
                    WHERE `time` IS NULL
                    AND `competition_id` = '".$id."'
                    AND `discipline` = 'HL'
                    AND `team_number` = -2
                ) ORDER BY `time`
            ) `all`
            GROUP BY `person_id`
        ) `best`
        INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
        INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
        ORDER BY `time`
    ");

    $zk = $db->getRows("
        SELECT
            0 AS `id`,
            `hl`.`person_id`,`p`.`name` AS `name`,`p`.`firstname` AS `firstname`,
            `hb`.`time` AS `hb`,
            `hl`.`time` AS `hl`,
            `hb`.`time` + `hl`.`time` AS `time`
        FROM (
            SELECT `person_id`,`time`
            FROM `scores`
            WHERE `time` IS NOT NULL
            AND `competition_id` = '".$id."'
            AND `discipline` = 'HL'
            AND `team_number` != -2
            ORDER BY `time`
        ) `hl`
        INNER JOIN (
            SELECT `person_id`,`time`
            FROM `scores`
            WHERE `time` IS NOT NULL
            AND `competition_id` = '".$id."'
            AND `discipline` = 'HB'
            AND `team_number` != -2
            ORDER BY `time`
        ) `hb` ON `hl`.`person_id` = `hb`.`person_id`
        INNER JOIN `persons` `p` ON `hb`.`person_id` = `p`.`id`
        GROUP BY `p`.`id`
        ORDER BY `time`
    ");

    $dis = array(
        'hb-female' => $hb['female'],
        'hb-female-final' => $hbFinale['female'],
        'hb-male' => $hb['male'],
        'hb-male-final' => $hbFinale['male'],
        'hl' => $hl,
        'hl--final' => $hlFinale,
        'zk' => $zk,
        'gs' => $gs,
        'fs-female' => $fs['female'],
        'fs-male' => $fs['male'],
        'la-female' => $la['female'],
        'la-male' => $la['male'],
    );

    echo
    '<h1>',
        htmlspecialchars($competition['event']),' - ',
        htmlspecialchars($competition['place']),' - ',
        gdate($competition['date']),
    '</h1>';

    echo '<div class="row">';
    echo '<div class="five columns">';
    echo '<div class="toc"><h5>Inhaltsverzeichnis</h5><ol>';
    foreach ($dis as $fullKey => $scores) {
        if (count($scores)) {
            $keys = explode('-', $fullKey);
            $key = $keys[0];
            $sex = false;
            $final = false;
            if (count($keys) > 1) {
                if (!empty($keys[1])) $sex = $keys[1];
                if (count($keys) > 2) {
                    $final = true;
                }
            }

            if ($final) {
                echo '<li><a href="#dis-'.$fullKey.'"title="',FSS::dis2name($key);
                if ($sex) echo ' '.FSS::sex($sex);
                echo ' - Finale">'.strtoupper($key);
                if ($sex) echo ' '.FSS::sex($sex);
                echo ' - Finale</a></li>';
            } else {
                echo '<li><a href="#dis-'.$fullKey.'">',FSS::dis2name($key);
                if ($sex) echo ' '.FSS::sex($sex);
                echo '</a></li>';

                if (in_array($key, array('hb', 'hl')) && $competition['score_type']) {
                    echo '<li><a href="#dis-'.$fullKey.'-mannschaft" title="',FSS::dis2name($key);
                    if ($sex) echo ' '.FSS::sex($sex);
                    echo ' - Mannschaftswertung">'.strtoupper($key);
                    if ($sex) echo ' '.FSS::sex($sex);
                    echo ' - Mannschaft</a></li>';
                }
            }
        }
    }

    echo '<li><a href="#toc-weblinks">Weblinks</a></li>';
    echo '<li><a href="#toc-files">Dateien</a></li>';
    echo '</ol></div></div>';


    echo '<div class="six columns"><table class="table">';

    echo '<tr><th colspan="2">Austragungsort:</th><td>'.Link::place($competition['place_id'], $competition['place']),'</td></tr>';
    echo '<tr><th colspan="2">Typ:</th><td>'.Link::event($competition['event_id'], $competition['event']),'</td></tr>';

    if ($competition['score_type']) {
        echo '<tr><th colspan="2">Mannschaftswertung:</th><td>',$competition['persons'],'/',$competition['run'],'/',$competition['score'],'<a class="helpinfo" data-file="mannschaftswertung">&nbsp;</a></td></tr>';
    } else {
        echo '<tr><th colspan="2">Mannschaftswertung:</th><td>Keine</td></tr>';
    }

    if ($competition['la']) echo '<tr><th colspan="2">Löschangriff:</th><td>',FSS::laType($competition['la']),'</td></tr>';
    if ($competition['fs']) echo '<tr><th colspan="2">4x100m:</th><td>',FSS::fsType($competition['fs']),'</td></tr>';

    echo '<tr><th colspan="2">Datum:</th><td>',gdate($competition['date']),'</td></tr>';
    echo '<tr><td colspan="3">&nbsp;</td></tr>';


    echo '<tr><td></td><th>Frauen</th><th>Männer</th></tr>';

    if (count($hb['female']) || count($hb['male']))
        echo '<tr title="Hindernisbahn"><th>HB:</th><td>',count($hb['female']),'</td><td>',count($hb['male']),'</td></tr>';

    if (count($hbFinale['female']) || count($hbFinale['male']))
        echo '<tr title="Hindernisbahn Finale"><th>Finale:</th><td>',count($hbFinale['female']),'</td><td>',count($hbFinale['male']),'</td></tr>';

    if (count($hl))
        echo '<tr title="Hakenleitersteigen"><th>HL:</th><td></td><td>',count($hl),'</td></tr>';

    if (count($hlFinale))
        echo '<tr title="Hakenleitersteigen Finale"><th>Finale:</th><td></td><td>',count($hlFinale),'</td></tr>';

    if (count($zk))
        echo '<tr title="Zweikampf"><th>ZK:</th><td></td><td>',count($zk),'</td></tr>';

    if (count($gs))
        echo '<tr title="Gruppenstafette"><th>GS:</th><td>',count($gs),'</td><td></td></tr>';

    if (count($fs['female']) || count($fs['male']))
        echo '<tr title="Feuerwehrstafette"><th>FS:</th><td>',count($fs['female']),'</td><td>',count($fs['male']),'</td></tr>';

    if (count($la['female']) || count($la['male']))
        echo '<tr title="Löschangriff"><th>HB:</th><td>',count($la['female']),'</td><td>',count($la['male']),'</td></tr>';


    echo '</table></div>';


    echo '<div class="four columns"><h4>Fehlversuche</h4>';
    echo '<img alt="" class="big" src="chart.php?type=competition_bad_good&amp;key=full&amp;id='.$id.'">';
    echo '</div>';

    echo '<div class="four columns" style="'.getMissedColor($competition['missed']).';padding:5px;border-radius:2px;"><h4>Status</h4>';

    $arr = explode(',', $competition['missed']);
    $out = array();
    foreach ($config['missed'] as $key => $value) {
        if (!in_array($key, $arr)) {
            $out[] = $value;
        }
    }
    if (count($out) === 0) {
        echo '<b>Vollständig erfasst</b>';
    } else {

        echo '<b>Es fehlen:</b>';
        echo '<ul class="disc">';
        foreach ($out as $o) echo '<li>'.$o.'</li>';
        echo '</ul>';
    }
    echo '</div>';


    echo '<div class="four columns">'.
        '<form class="excel-box" method="post" action="excel.php?competition='.$id.'" id="form-excel">'.
            '<input type="hidden" name="competition_id" value="'.$id.'"/>'.
            '<img src="styling/images/excel.png" alt="excel" style="float:right"/>'.
            'Daten als Excel-Datei herunterladen.'.
        '</form></div>';


    foreach ($dis as $fullKey => $scores) {
        if (!count($scores)) continue;

        $keys = explode('-', $fullKey);
        $key = $keys[0];
        $sex = false;
        $final = false;
        if (count($keys) > 1) {
            if (!empty($keys[1])) $sex = $keys[1];
            if (count($keys) > 2) {
                $final = true;
            }
        }

        if (in_array($key, array('hb', 'hl', 'zk'))) {
            $sum = 0;
            $i = 0;
            $sum5 = 0;
            $i5 = 0;
            $sum10 = 0;
            $i10 = 0;
            foreach ($scores as $score) {
                if (FSS::isInvalid($score['time'])) continue;

                $sum += $score['time'];
                $i++;
                if ($i5 < 5) {
                    $sum5 += $score['time'];
                    $i5++;
                }
                if ($i10 < 10) {
                    $sum10 += $score['time'];
                    $i10++;
                }
            }
            $ave = $sum/$i;
            $ave5 = $sum5/$i5;
            $ave10 = $sum10/$i10;

            echo '<h2 id="dis-'.$fullKey.'">',FSS::dis2name($key);
            if ($sex) echo ' '.FSS::sex($sex);
            if ($final) echo ' - Finale';
            echo '</h2>';

            echo  '<table class="chart-table">',
                '<tr><th>Bestzeit:</th><td>',FSS::time($scores[0]['time']),'</td></tr>',
                '<tr><th>Wettkämpfer:</th><td>',count($scores),'</td></tr>',
                '<tr><th>Durchschnitt:</th><td>',FSS::time($ave),'</td></tr>';

            if ($i5 == 5) echo '<tr title="Durchschnitt der besten Fünf"><th>Beste 5:</th><td>',FSS::time($ave5),'</td></tr>';
            if ($i10 == 10) echo '<tr title="Durchschnitt der besten Zehn"><th>Beste 10:</th><td>',FSS::time($ave10),'</td></tr>';
            if ($key != 'zk') echo '<tr><td style="text-align:center;" colspan="2"><img alt="" class="big" src="chart.php?type=competition_bad_good&amp;key='.$fullKey.'&amp;id='.$id.'"></td></tr>';

            echo '</table>';
            echo '<p class="chart"><img class="infochart big" data-file="competition_platzierung" src="chart.php?type=competition&amp;key='.$fullKey.'&amp;id='.$id.'" style="width:700px;height:230px"/></p>';

            echo
              '<table class="datatable sc_'.$key;
            if ($final) echo '-final';
            echo '"><thead><tr>',
                '<th style="width:18%">Name</th>',
                '<th style="width:17%">Vorname</th>';

            if (!$final && $key != 'zk') echo '<th style="width:30%">Mannschaft</th>';

            if (!$final && $key != 'zk' && $competition['score_type']) {
                echo '<th style="width:5%">W</th>';
            }

            if ($key == 'zk') {
                echo '<th style="width:10%">HB</th><th style="width:10%">HL</th>';
            }

            echo
                '<th style="width:10%">Zeit</th>',
                '<th style="width:10%"></th>',
              '</tr></thead>';
            echo '<tbody>';

            foreach ($scores as $score) {

                echo '<tr data-id="',$score['id'],'">',
                      '<td>',htmlspecialchars($score['name']),'</td>',
                      '<td>',htmlspecialchars($score['firstname']),'</td>';

                if (!$final && $key != 'zk') {
                        echo '<td class="team">';
                    if ($score['team']) {
                        echo Link::team($score['team_id'],$score['team']);
                    }
                    echo '</td>';

                    if ($competition['score_type']) {
                        $mannschaft = FSS::teamNumber($score['team_number']);

                        echo '<td title="Person ist ';
                        if ($mannschaft == 'E') {
                            echo 'als Einzelstarter';
                        } else {
                            echo 'in Mannschaft '.$mannschaft;
                        }
                        echo ' gestartet" class="number">'.$mannschaft.'</td>';
                    }
                }

                if ($key == 'zk') {
                    echo '<td title="Hindernisbahn">',FSS::time($score['hb']),'</td>';
                    echo '<td title="Hakenleitersteigen">',FSS::time($score['hl']),'</td>';
                }

                echo
                      '<td title="Beste Zeit dieses Sportlers bei diesem Wettkampf">',FSS::time($score['time']),'</td>',
                      '<td>'.Link::person($score['person_id'], 'Details', $score['firstname'], $score['name']),'</td>',
                    '</tr>';
            }
            echo '</tbody></table>';


            // Mannschaftswertung
            if (!$final && $key != 'zk' && $competition['score_type']) {
                echo '<h2 id="dis-'.$fullKey.'-mannschaft">',FSS::dis2name($key);
                if ($sex) echo ' '.FSS::sex($sex);
                if ($final) echo ' - Mannschaftswertung';
                echo '</h2>';

                // Bereche die Wertung
                $teams = array();
                foreach ($scores as $score) {
                    if ($score['team_number'] < 0) continue;
                    if (!$score['team_id']) continue;

                    $uniqTeam = $score['team_id'].$score['team_number'];
                    if (!isset($teams[$uniqTeam])) {
                        $teams[$uniqTeam] = array(
                            'name' => $score['team'],
                            'short' => $score['shortteam'],
                            'id' => $score['team_id'],
                            'number' => $score['team_number'],
                            'scores' => array(),
                        );
                    }

                    $teams[$uniqTeam]['scores'][] = $score;
                }

                // sort every persons in teams
                foreach ($teams as $uniqTeam => $team) {
                    $time = 0;

                    usort($team['scores'], function($a, $b) {
                        if ($a['time'] == $b['time']) return 0;
                        elseif ($a['time'] > $b['time']) return 1;
                        else return -1;
                    });

                    if (count($team['scores']) < $competition['score']) {

                        $teams[$uniqTeam]['time'] = FSS::INVALID;
                        continue;
                    }

                    for($i = 0; $i < $competition['score']; $i++) {
                        if ($team['scores'][$i]['time'] == FSS::INVALID) {
                            $teams[$uniqTeam]['time'] = FSS::INVALID;
                            continue 2;
                        }
                        $time += $team['scores'][$i]['time'];
                    }
                    $teams[$uniqTeam]['time'] = $time;
                }

                // Sortiere Teams nach Zeit
                uasort($teams, function ($a, $b) {
                    if ($a['time'] == $b['time']) return 0;
                    elseif ($a['time'] > $b['time']) return 1;
                    else return -1;
                });

                echo '<table class="table">';

                foreach ($teams as $uniqTeam => $team) {
                    echo '<tr>';
                    echo '<td>'.Link::team($team['id'], $team['short']).'</td>';
                    echo '<td>'.FSS::time($team['time']).'</td>';

                    $inScore = array();
                    $outScore = array();
                    $i = 0;
                    foreach ($team['scores'] as $score) {
                        $link = Link::person($score['person_id'], 'sub', $score['name'], $score['firstname'], FSS::time($score['time']));
                        if ($i < $competition['score']) $inScore[] = $link;
                        else $outScore[] = $link;
                        $i++;
                    }

                    echo '<td style="font-size:0.9em">'.implode(', ', $inScore).'</td>';
                    echo '<td style="font-size:0.9em">'.implode(', ', $outScore).'</td>';
                    echo '<td';
                    if (count($team['scores']) > $competition['run']) echo ' style="background:FF0000"';
                    echo '>'.count($team['scores']).' von '.$competition['run'].'</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }

            $current_files = array();
            foreach ($files as $file) {
                if (in_array($key, $file['content'])) {
                    $current_files[] = $file;
                }
            }

            if (count($current_files)) {
                echo '<div style="float:right;clear:both;width:350px;" class="toc">';
                echo '<h5>Verknüpfte Ergebnisse</h5>';
                echo '<ul class="disc">';
                foreach ($current_files as $file) {
                    echo '<li><a href="files/'.$id.'/'.$file['name'].'">'.$file['name'].'</a></li>';
                }
                echo '</ul>';
                echo '</div>';
            }


        } else {

            echo '<h2 id="dis-'.$fullKey.'">',FSS::dis2name($key);
            if ($sex) echo ' '.FSS::sex($sex);
            echo '</h2>';

            $sum = 0;
            $i = 0;
            foreach ($scores as $score) {
                if (FSS::isInvalid($score['time'])) continue;
                $sum += $score['time'];
                $i++;
            }
            $ave = $sum/$i;

            echo  '<table class="chart-table">',
                    '<tr><th>Bestzeit:</th><td>',FSS::time($scores[0]['time']),'</td></tr>',
                    '<tr><th>Mannschaften:</th><td>',count($scores),'</td></tr>',
                    '<tr><th>Durchschnitt:</th><td>',FSS::time($ave),'</td></tr>',
                    '<tr><td style="text-align:center;" colspan="2"><img alt="" class="big" src="chart.php?type=competition_bad_good&amp;key='.$fullKey.'&amp;id='.$_id.'"/></td></tr>',
                  '</table>';
            echo '<p class="chart"><img src="chart.php?type=competition&amp;key='.$fullKey.'&amp;id='.$_id.'" style="width:700px;height:230px" class="big infochart" data-file="competition_platzierung" /></p>';

            echo '<table class="datatable sc_'.$key.'"><thead><tr>',
                    '<th style="width:14%">Team</th>',
                    '<th style="width:8%">Zeit</th>';

            for ($wk = 1; $wk < 8; $wk++) {
                if (array_key_exists('person_'.$wk, $scores[0])) {
                    echo '<th style="width:13%" title="'.WK::type($wk, $sex, $key).'">WK'.$wk.'</th>';
                }
            }

            echo '</tr></thead><tbody>';

            foreach ($scores as $score) {
                echo
                    '<tr data-id="',$score['id'],'">',
                        '<td>'.Link::team($score['team_id'], $score['shortteam'].' '.FSS::teamNumber($score['team_number']), 'Details zu '.$score['team'].' anzeigen'),'</td>',
                        '<td>',FSS::time($score['time']),'</td>';

                for ($wk = 1; $wk < 8; $wk++) {
                    if (array_key_exists('person_'.$wk, $scores[0])) {
                        echo '<td style="font-size:0.8em" class="person">';
                        if (!empty($score['person_'.$wk])) {
                            echo Link::person($score['person_'.$wk], 'sub', $score['name'.$wk], $score['firstname'.$wk]);
                        }
                        echo '</td>';
                    }
                }
                echo '</tr>';
            }
            echo '</tbody></table>';


            $current_files = array();
            foreach ($files as $file) {
                if (in_array($key, $file['content'])) {
                    $current_files[] = $file;
                }
            }
            if (count($current_files)) {
                echo '<div style="float:right;clear:both;width:350px;" class="toc">';
                echo '<h5>Verknüpfte Ergebnisse</h5>';
                echo '<ul class="disc">';
                foreach ($current_files as $file) {
                    echo '<li><a href="files/'.$id.'/'.$file['name'].'">'.$file['name'].'</a></li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        }
    }


    $links = $db->getRows("
      SELECT *
      FROM `links`
      WHERE `for_id` = '".$id."'
      AND `for` = 'competition'
    ");

    echo '<h2 id="toc-weblinks">Weblinks zu diesem Wettkampf</h2>';
    if (count($links)) {
        echo '<ul class="disc">';
        foreach ($links as $link) {
            echo '<li><a href="',htmlspecialchars($link['url']),'">',htmlspecialchars($link['name']),'</a></li>';
        }
        echo '</ul>';
    }
    echo '<button id="add-link" data-for-id="'.$id.'" data-for-table="competition">Link hinzufügen</button>';

    echo '</div>';



    echo '<h2 id="toc-files">Dateien zu diesem Wettkampf</h2>';

    if (count($files)) {
        $c_types = array(
            'hl'  =>  'HL',
            'hbm' =>  'HB m',
            'hbw' =>  'HB w',
            'gs'  =>  'GS',
            'law' =>  'LA w',
            'lam' =>  'LA m',
            'fsw' =>  'FS w',
            'fsm' =>  'FS m'
        );

        echo '<table class="table"><tr><th>Dateien</th><th>Enthaltene Ergebnisse</th></tr>';
        foreach ($files as $file) {
            echo '<tr><td><a href="files/',$id,'/',$file['name'],'">',$file['name'],'</a></td><td>';

            $current_types = array();
            foreach ($c_types as $t => $n) {
                if (in_array($t, $file['content'])) {
                    $current_types[] = '<span>'.$n.'</span>';
                }
            }
            echo implode(', ', $current_types);

            echo '</td></tr>';
        }
        echo '</table>';
    }

    echo '<button id="add-file">Datei hinzufügen</button>';

    echo '<div id="add-file-form" style="display:none;">
        <form action="?page=competition_upload" method="post" enctype="multipart/form-data">
            <h3>Es dürfen nur PDFs hochgeladen werden.</h3>
            <table class="table">
                <tr><th rowspan="2">Datei</th><th colspan="8">Folgende Ergebnisse sind in dieser Datei enthalten</th></tr>
                <tr><th>HL</th><th>HB w</th><th>HB m</th><th>GS</th><th>LA w</th><th>LA m</th><th>FS w</th><th>FS m</th></tr>
                <tr class="input-file-row"><td><input type="file" name="result_0" /></td>
                    <td title="Hakenleitersteigen"><input type="checkbox" name="hl_0" value="true"/></td>
                    <td title="Hindernisbahn weiblich"><input type="checkbox" name="hbw_0" value="true"/></td>
                    <td title="Hindernisbahn männlich"><input type="checkbox" name="hbm_0" value="true"/></td>
                    <td title="Gruppenstafette"><input type="checkbox" name="gs_0" value="true"/></td>
                    <td title="Löschangriff weiblich"><input type="checkbox" name="law_0" value="true"/></td>
                    <td title="Löschangriff männlich"><input type="checkbox" name="lam_0" value="true"/></td>
                    <td title="Feuerwehrstafette weiblich"><input type="checkbox" name="fsw_0" value="true"/></td>
                    <td title="Feuerwehrstafette männlich"><input type="checkbox" name="fsm_0" value="true"/></td>
                </tr>
            </table>
            <p>
                <input type="hidden" name="id" value="'.$id.'"/>
                <a id="more-files" href="">Noch eine Datei auswählen</a> <br/>
                <button>Hochladen</button>
            </p>
        </form>
    </div>
    ';
}

