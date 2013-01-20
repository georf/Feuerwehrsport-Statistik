<?php

$sexConfig = array(
    'female' => 'weiblich',
    'male' => 'männlich',
);

if (!isset($_GET['id']) || !Check::isIn($_GET['id'], 'teams')) throw new PageNotFound();


$_id = $_GET['id'];

$cache = Cache::get();
if ($cache) {
    echo $cache;
} else {
    ob_start();

    $team = $db->getFirstRow("
        SELECT *
        FROM `teams`
        WHERE `id` = '".$db->escape($_id)."'
    ");

    $id = $team['id'];

    echo dataDiv($team, 'team');

    $members = array();
    $member = array(
        'HB' => 0,
        'GS' => 0,
        'LA' => 0,
        'FS' => 0,
        'HL' => 0,
        'mem_id' => null
    );



    // Hindernisbahn
    $scores = $db->getRows("
        SELECT `person_id`
        FROM `scores`
        WHERE `team_id` = '".$id."'
        AND `discipline` = 'HB'
    ");
    foreach ($scores as $score) {
        $pid = $score['person_id'];
        if (!isset($members[$pid])) $members[$pid] = $member;

        $members[$pid]['HB']++;
    }



    // Hakenleiter
    $scores = $db->getRows("
        SELECT `person_id`
        FROM `scores`
        WHERE `team_id` = '".$id."'
        AND `discipline` = 'HL'
    ");
    foreach ($scores as $score) {
        $pid = $score['person_id'];
        if (!isset($members[$pid])) $members[$pid] = $member;

        $members[$pid]['HL']++;
    }




    // Gruppenstafette
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`
        FROM `scores_gruppenstafette`
        WHERE `team_id` = '".$id."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 6; $i++) {

            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = $member;

            $members[$pid]['GS']++;
        }
    }




    // Löschangriff
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`
        FROM `scores_loeschangriff`
        WHERE `team_id` = '".$id."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 7; $i++) {

            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = $member;

            $members[$pid]['LA']++;
        }
    }




    // Feuerwehrstafette
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`
        FROM `scores_stafette`
        WHERE `team_id` = '".$id."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 7; $i++) {

            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = $member;

            $members[$pid]['FS']++;
        }
    }

    foreach ($members as $pid=>$member) {
        $m = $db->getFirstRow("
            SELECT `name`, `firstname`, `sex`
            FROM `persons`
            WHERE `id` = '".$pid."'
            LIMIT 1;
        ");
        $members[$pid]['firstname'] = $m['firstname'];
        $members[$pid]['name'] = $m['name'];
        $members[$pid]['sex'] = $m['sex'];
    }


    /*
     * Begin der Berechnung der Zeiten
     */

    $sc_gs = $db->getRows("
        SELECT `g`.*,
            `c`.`id` AS `c_id`,`c`.`date` AS `c_date`,
            `e`.`id` AS `e_id`,`e`.`name` AS `e_name`,
            `p`.`id` AS `p_id`,`p`.`name` AS `p_name`,
            `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
            `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
            `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
            `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`,
            `p5`.`name` AS `name5`,`p5`.`firstname` AS `firstname5`,
            `p6`.`name` AS `name6`,`p6`.`firstname` AS `firstname6`
        FROM (
            (
                SELECT `id`,`team_number`,`competition_id`,
                `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,
                `time`
                FROM `scores_gruppenstafette` `gC`
                WHERE `time` IS NOT NULL
                AND `gC`.`team_id` = '".$id."'
            ) UNION (
                SELECT `id`,`team_number`,`competition_id`,
                `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,
                ".FSS::INVALID." AS `time`
                FROM `scores_gruppenstafette` `gD`
                WHERE `time` IS NULL
                AND `gD`.`team_id` = '".$id."'
            ) ORDER BY `time`
        ) `g`

        INNER JOIN `competitions` `c` ON `c`.`id` = `g`.`competition_id`
        INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
        INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
        LEFT JOIN `persons` `p1` ON `g`.`person_1` = `p1`.`id`
        LEFT JOIN `persons` `p2` ON `g`.`person_2` = `p2`.`id`
        LEFT JOIN `persons` `p3` ON `g`.`person_3` = `p3`.`id`
        LEFT JOIN `persons` `p4` ON `g`.`person_4` = `p4`.`id`
        LEFT JOIN `persons` `p5` ON `g`.`person_5` = `p5`.`id`
        LEFT JOIN `persons` `p6` ON `g`.`person_6` = `p6`.`id`
        ORDER BY `c_date` DESC
    ");


    $sc_fs = $sexConfig;
    foreach ($sc_fs as $sex => $name) {
        $sc_fs[$sex] = array();
        $sc_fs[$sex]['name'] = $name;
        $sc_fs[$sex]['scores'] = $db->getRows("
            SELECT `g`.*,
                `c`.`id` AS `c_id`,`c`.`date` AS `c_date`,
                `e`.`id` AS `e_id`,`e`.`name` AS `e_name`,
                `p`.`id` AS `p_id`,`p`.`name` AS `p_name`,
                `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
                `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
                `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
                `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`
            FROM (
                    (
                        SELECT `id`,`team_number`,`competition_id`,
                        `person_1`,`person_2`,`person_3`,`person_4`,
                        `time`
                        FROM `scores_stafette` `gC`
                        WHERE `time` IS NOT NULL
                        AND `gC`.`team_id` = '".$id."'
                        AND `gC`.`sex` = '".$sex."'
                    ) UNION (
                        SELECT `id`,`team_number`,`competition_id`,
                        `person_1`,`person_2`,`person_3`,`person_4`,
                        ".FSS::INVALID." AS `time`
                        FROM `scores_stafette` `gD`
                        WHERE `time` IS NULL
                        AND `gD`.`team_id` = '".$id."'
                        AND `gD`.`sex` = '".$sex."'
                    ) ORDER BY `time`

            ) `g`

            INNER JOIN `competitions` `c` ON `c`.`id` = `g`.`competition_id`
            INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
            INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
            LEFT JOIN `persons` `p1` ON `g`.`person_1` = `p1`.`id`
            LEFT JOIN `persons` `p2` ON `g`.`person_2` = `p2`.`id`
            LEFT JOIN `persons` `p3` ON `g`.`person_3` = `p3`.`id`
            LEFT JOIN `persons` `p4` ON `g`.`person_4` = `p4`.`id`
            ORDER BY `c_date` DESC
        ");
    }


    $sc_la = $sexConfig;
    foreach ($sc_la as $sex => $name) {
        $sc_la[$sex] = array();
        $sc_la[$sex]['name'] = $name;
        $sc_la[$sex]['scores'] = $db->getRows("
            SELECT `g`.*,
                `c`.`id` AS `c_id`,`c`.`date` AS `c_date`,
                `e`.`id` AS `e_id`,`e`.`name` AS `e_name`,
                `p`.`id` AS `p_id`,`p`.`name` AS `p_name`,
                `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
                `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
                `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
                `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`,
                `p5`.`name` AS `name5`,`p5`.`firstname` AS `firstname5`,
                `p6`.`name` AS `name6`,`p6`.`firstname` AS `firstname6`,
                `p7`.`name` AS `name7`,`p7`.`firstname` AS `firstname7`
            FROM (
                    (
                        SELECT `id`,`team_number`,`competition_id`,
                        `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`,
                        `time`
                        FROM `scores_loeschangriff` `gC`
                        WHERE `time` IS NOT NULL
                        AND `gC`.`team_id` = '".$id."'
                        AND `gC`.`sex` = '".$sex."'
                    ) UNION (
                        SELECT `id`,`team_number`,`competition_id`,
                        `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`,
                        ".FSS::INVALID." AS `time`
                        FROM `scores_loeschangriff` `gD`
                        WHERE `time` IS NULL
                        AND `gD`.`team_id` = '".$id."'
                        AND `gD`.`sex` = '".$sex."'
                    ) ORDER BY `time`

            ) `g`

            INNER JOIN `competitions` `c` ON `c`.`id` = `g`.`competition_id`
            INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
            INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
            LEFT JOIN `persons` `p1` ON `g`.`person_1` = `p1`.`id`
            LEFT JOIN `persons` `p2` ON `g`.`person_2` = `p2`.`id`
            LEFT JOIN `persons` `p3` ON `g`.`person_3` = `p3`.`id`
            LEFT JOIN `persons` `p4` ON `g`.`person_4` = `p4`.`id`
            LEFT JOIN `persons` `p5` ON `g`.`person_5` = `p5`.`id`
            LEFT JOIN `persons` `p6` ON `g`.`person_6` = `p6`.`id`
            LEFT JOIN `persons` `p7` ON `g`.`person_7` = `p7`.`id`
            ORDER BY `c_date` DESC
        ");
    }


    // Team - Logo
    if ($team['logo']) {
        echo '<p style="float:left;margin-right:20px;"><img src="'.$config['logo-path'].$team['logo'].'" alt="'.htmlspecialchars($team['short']).'"/></p>';
    }

    // Verteilung Geschlechter
    echo '<p style="float:right;margin-right:20px;"><img src="chart.php?type=team_sex&id='.$team['id'].'" alt=""/></p>';

    // Kein Team Logo
    if (empty($team['logo'])) {
        echo '<p style="margin-right:80px;border:3px solid #FF7D6E; background:#FFD8D3; padding:2px;float:right">Es fehlt noch ein Logo für dieses Team.<br/>Sende doch ein Logo zu!<a class="helpinfo" data-file="logosenden">&nbsp;</a></p>';
    }

    // Überschrift
    echo '<h1>',htmlspecialchars($team['name']),'</h1>';
    echo '<table>
    <tr><th>Mitglieder:</th><td>'.count($members).'</td></tr>
    <tr><th>Webseite:</th><td>';

    $links = $db->getRows("
      SELECT *
      FROM `links`
      WHERE `for_id` = '".$id."'
      AND `for` = 'team'
    ");

    foreach ($links as $link) {
        echo '<a href="',htmlspecialchars($link['url']),'">',htmlspecialchars($link['name']),'</a><br/>';
    }
    echo '<span class="bt applications-internet-add" id="add-link" data-for-id="'.$id.'" data-for-table="team" title="Link hinzufügen"></span>';
    echo '</td></tr>
    </table>';


    echo
      '<table class="datatable datatable-sort-members"><thead><tr>',
        '<th style="width:22%">Name</th>',
        '<th style="width:22%">Vorname</th>',
        '<th style="width:22%">Geschlecht</th>',
        '<th style="width:5%">HB</th>',
        '<th style="width:5%">GS</th>',
        '<th style="width:5%">LA</th>',
        '<th style="width:5%">FS</th>',
        '<th style="width:5%">HL</th>',
        '<th style="width:16%"></th>',
      '</tr></thead>',
      '<tbody>';
    foreach ($members as $pid => $member) {
        echo
            '<tr data-person-id="',$pid,'" data-mem-id="',$member['mem_id'],'">',
              '<td>',htmlspecialchars($member['name']),'</td>',
              '<td>',htmlspecialchars($member['firstname']),'</td>',
              '<td>',FSS::sex($member['sex']),'</td>',
              '<td>',$member['HB'],'</td>',
              '<td>',$member['GS'],'</td>',
              '<td>',$member['LA'],'</td>',
              '<td>',$member['FS'],'</td>',
              '<td>',$member['HL'],'</td>',
              '<td>'.Link::person($pid, 'Details', $member['name'], $member['firstname']).'</td>',
            '</tr>';
    }
    echo '</tbody></table>';


    if (count($sc_gs)) {
        echo '<h2 id="toc-sc_gs">Gruppenstafette</h2>';
        echo '<table class="datatable datatable-sort-gs sc_gs"><thead><tr>',
                '<th style="width:10%">Typ</th>',
                '<th style="width:14%">Ort</th>',
                '<th style="width:12%">Datum</th>',
                '<th style="width:2%">N</th>',
                '<th style="width:8%">Zeit</th>',
                '<th style="width:9%" title="'.WK::gs(1).'">WK1</th>',
                '<th style="width:9%" title="'.WK::gs(2).'">WK2</th>',
                '<th style="width:9%" title="'.WK::gs(3).'">WK3</th>',
                '<th style="width:9%" title="'.WK::gs(4).'">WK4</th>',
                '<th style="width:9%" title="'.WK::gs(5).'">WK5</th>',
                '<th style="width:9%" title="'.WK::gs(6).'">WK6</th>',
                '<th style="width:2%"></th>',
              '</tr></thead>';
        echo '<tbody>';


        foreach ($sc_gs as $score) {
            echo
            '<tr data-id="',$score['id'],'">',
                '<td style="font-size:0.7em;">'.Link::event($score['e_id'], $score['e_name']).'</td>',
                '<td style="font-size:0.7em;">'.Link::place($score['p_id'], $score['p_name']).'</td>',
                '<td>'.$score['c_date'].'</td>',
                '<td>'.FSS::teamNumber($score['team_number']).'</td>',
                '<td>',FSS::time($score['time']),'</td>';

            for ($wk = 1; $wk < 7; $wk++) {
                echo '<td class="person" style="font-size:0.7em;" title="'.WK::gs($wk).'">';
                if (!empty($score['person_'.$wk])) {
                    echo Link::subPerson($score['person_'.$wk], $score['name'.$wk], $score['firstname'.$wk]);
                }
                echo '</td>';
            }

            echo '<td>'.Link::competition($score['c_id']).'</td>',
                '</tr>';
        }
        echo '</tbody></table>';
    }


    foreach ($sc_fs as $sex => $content) {
        if (count($content['scores'])) {
            echo '<h2 id="toc-fs-'.$sex.'">Feuerwehrstafette '.$content['name'].'</h2>';
            echo '<table class="datatable datatable-sort-fs sc_fs"><thead><tr>',
                    '<th style="width:9%">Typ</th>',
                    '<th style="width:9%">Ort</th>',
                    '<th style="width:8%">Datum</th>',
                    '<th style="width:3%">N</th>',
                    '<th style="width:8%">Zeit</th>',
                    '<th style="width:12%" title="'.WK::fs(1, $sex).'">WK1</th>',
                    '<th style="width:12%" title="'.WK::fs(2, $sex).'">WK2</th>',
                    '<th style="width:12%" title="'.WK::fs(3, $sex).'">WK3</th>',
                    '<th style="width:12%" title="'.WK::fs(4, $sex).'">WK4</th>',
                    '<th style="width:3%"></th>',
                  '</tr></thead>';
            echo '<tbody>';

            foreach ($content['scores'] as $score) {
                echo
                '<tr data-id="',$score['id'],'">',
                    '<td style="font-size:0.7em;">'.Link::event($score['e_id'], $score['e_name']).'</td>',
                    '<td style="font-size:0.7em;">'.Link::place($score['p_id'], $score['p_name']).'</td>',
                    '<td style="font-size:0.8em;">'.$score['c_date'].'</td>',
                    '<td style="font-size:0.8em;">'.FSS::teamNumber($score['team_number']).'</td>',
                    '<td style="font-size:0.9em;">',FSS::time($score['time']),'</td>';

                for ($wk = 1; $wk < 5; $wk++) {
                    echo '<td class="person" style="font-size:0.7em;" title="'.WK::fs($wk, $sex).'">';
                    if (!empty($score['person_'.$wk])) {
                        echo Link::subPerson($score['person_'.$wk], $score['name'.$wk], $score['firstname'.$wk]);
                    }
                    echo '</td>';
                }
                echo '<td style="font-size:0.9em;">'.Link::competition($score['c_id']).'</td>',
                    '</tr>';
            }
            echo '</tbody></table>';
        }
    }


    foreach ($sc_la as $sex => $content) {
        if (count($content['scores'])) {
            echo '<h2 id="toc-la-'.$sex.'">Löschangriff '.$content['name'].'</h2>';
            echo '<table class="datatable datatable-sort-la sc_la"><thead><tr>',
                    '<th style="width:4%">Typ</th>',
                    '<th style="width:4%">Ort</th>',
                    '<th style="width:6%">Datum</th>',
                    '<th style="width:2%">N</th>',
                    '<th style="width:6%">Zeit</th>',
                    '<th style="width:10%" title="'.WK::la(1).'">WK1</th>',
                    '<th style="width:10%" title="'.WK::la(2).'">WK2</th>',
                    '<th style="width:10%" title="'.WK::la(3).'">WK3</th>',
                    '<th style="width:10%" title="'.WK::la(4).'">WK4</th>',
                    '<th style="width:10%" title="'.WK::la(5).'">WK5</th>',
                    '<th style="width:10%" title="'.WK::la(6).'">WK6</th>',
                    '<th style="width:10%" title="'.WK::la(7).'">WK7</th>',
                    '<th style="width:2%"></th>',
                  '</tr></thead>';
            echo '<tbody>';


            foreach ($content['scores'] as $score) {
                echo
                '<tr data-id="',$score['id'],'">',
                    '<td style="font-size:0.7em;">'.Link::event($score['e_id'], $score['e_name']).'</td>',
                    '<td style="font-size:0.7em;">'.Link::place($score['p_id'], $score['p_name']).'</td>',
                    '<td style="font-size:0.7em;">'.$score['c_date'].'</td>',
                    '<td style="font-size:0.7em;">'.FSS::teamNumber($score['team_number']).'</td>',
                    '<td style="font-size:0.9em;">',FSS::time($score['time']),'</td>';

                for ($wk = 1; $wk < 8; $wk++) {
                    echo '<td class="person" style="font-size:0.7em;" title="'.WK::la($wk).'">';

                    if (!empty($score['person_'.$wk])) {
                        echo Link::subPerson($score['person_'.$wk], $score['name'.$wk], $score['firstname'.$wk]);
                    }

                    echo '</td>';
                }
                echo '<td style="font-size:0.9em;">'.Link::competition($score['c_id']).'</td>',
                    '</tr>';
            }
            echo '</tbody></table>';
        }
    }


echo '
    <h2 id="fehler">Fehler melden</h2>
    <p>Beim Importieren der Ergebnisse kann es immer wieder mal zu Fehlern kommen. Geraden wenn die Namen in den Ergebnislisten verkehrt geschrieben wurden, kann keine eindeutige Zuordnung stattfinden. Außerdem treten auch Probleme mit Umlauten oder anderen besonderen Buchstaben im Namen auf.</p>
    <p>Ihr könnt jetzt beim Korrigieren der Daten helfen. Dafür klickt ihr auf folgenden Link und generiert eine Meldung für den Administrator. Dieser überprüft dann die Eingaben und leitet weitere Schritte ein.</p>
    <p><button id="report-error">Fehler mit diesem Team melden</button></p>
    ';

    Cache::put(ob_get_flush());
}
