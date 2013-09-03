<?php

$sexConfig = array(
    'female' => 'weiblich',
    'male' => 'männlich',
);

if (!isset($_GET['id']) || !Check::isIn($_GET['id'], 'teams')) throw new PageNotFound();


$_id = $_GET['id'];


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
    FROM `scores_gs`
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
    FROM `scores_la`
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
    FROM `scores_fs`
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
            FROM `scores_gs` `gC`
            WHERE `time` IS NOT NULL
            AND `gC`.`team_id` = '".$id."'
        ) UNION (
            SELECT `id`,`team_number`,`competition_id`,
            `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,
            ".FSS::INVALID." AS `time`
            FROM `scores_gs` `gD`
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
            `c`.`fs` AS `type`,
            `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
            `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
            `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
            `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`
        FROM (
                (
                    SELECT `id`,`team_number`,`competition_id`,
                    `person_1`,`person_2`,`person_3`,`person_4`,
                    `time`
                    FROM `scores_fs` `gC`
                    WHERE `time` IS NOT NULL
                    AND `gC`.`team_id` = '".$id."'
                    AND `gC`.`sex` = '".$sex."'
                ) UNION (
                    SELECT `id`,`team_number`,`competition_id`,
                    `person_1`,`person_2`,`person_3`,`person_4`,
                    ".FSS::INVALID." AS `time`
                    FROM `scores_fs` `gD`
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
            `c`.`la` AS `type`,
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
                    FROM `scores_la` `gC`
                    WHERE `time` IS NOT NULL
                    AND `gC`.`team_id` = '".$id."'
                    AND `gC`.`sex` = '".$sex."'
                ) UNION (
                    SELECT `id`,`team_number`,`competition_id`,
                    `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`,
                    ".FSS::INVALID." AS `time`
                    FROM `scores_la` `gD`
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


// Mannschaftswertung
$team_scores = array(
    'hb-female' => array(),
    'hb-male' => array(),
    'hl' => array(),
);

$competitions = $db->getRows("
    SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`,
        `t`.`persons`,`t`.`run`,`t`.`score`,`t`.`id` AS `score_type`
    FROM `competitions` `c`
    INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
    INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
    LEFT JOIN `score_types` `t` ON `t`.`id` = `c`.`score_type_id`
    WHERE `c`.`score_type_id` != 0
    ORDER BY `c`.`date` DESC
");

foreach ($competitions as $c_id => $competition) {
    $single_scores = array();
    foreach (array('female', 'male') as $sex) {

        $single_scores['hb-'.$sex] = $db->getRows("
            SELECT `best`.*,
                `p`.`name` AS `name`,`p`.`firstname` AS `firstname`
            FROM (
                SELECT *
                FROM (
                    (
                        SELECT `id`,`team_number`,
                        `person_id`,
                        `time`
                        FROM `scores`
                        WHERE `time` IS NOT NULL
                        AND `competition_id` = '".$competition['id']."'
                        AND `discipline` = 'HB'
                        AND `team_number` != -2
                        AND `team_id` = '".$id."'
                    ) UNION (
                        SELECT `id`,`team_number`,
                        `person_id`,
                        ".FSS::INVALID." AS `time`
                        FROM `scores`
                        WHERE `time` IS NULL
                        AND `competition_id` = '".$competition['id']."'
                        AND `discipline` = 'HB'
                        AND `team_number` != -2
                        AND `team_id` = '".$id."'
                    ) ORDER BY `time`
                ) `all`
                GROUP BY `person_id`
            ) `best`
            INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
            WHERE `sex` = '".$sex."'
            ORDER BY `time`
        ");
    }

    $single_scores['hl'] = $db->getRows("
        SELECT `best`.*,
            `p`.`name` AS `name`,`p`.`firstname` AS `firstname`
        FROM (
            SELECT *
            FROM (
                (
                    SELECT `id`,`team_number`,
                    `person_id`,
                    `time`
                    FROM `scores`
                    WHERE `time` IS NOT NULL
                    AND `competition_id` = '".$competition['id']."'
                    AND `discipline` = 'HL'
                    AND `team_number` != -2
                    AND `team_id` = '".$id."'
                ) UNION (
                    SELECT `id`,`team_number`,
                    `person_id`,
                    ".FSS::INVALID." AS `time`
                    FROM `scores`
                    WHERE `time` IS NULL
                    AND `competition_id` = '".$competition['id']."'
                    AND `discipline` = 'HL'
                    AND `team_number` != -2
                    AND `team_id` = '".$id."'
                ) ORDER BY `time`
            ) `all`
            GROUP BY `person_id`
        ) `best`
        INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
        ORDER BY `time`
    ");

    foreach ($single_scores as $key => $dis) {
        if (!count($dis)) continue;

        // Bereche die Wertung
        $teams = array();
        foreach ($dis as $score) {
            if ($score['team_number'] < 0) continue;


            $uniqTeam = $score['team_number'];
            if (!isset($teams[$uniqTeam])) {
                $teams[$uniqTeam] = array(
                    'number' => $score['team_number'],
                    'scores' => array(),
                    'time' => FSS::INVALID,
                    'time68' => -1
                );
            }

            $teams[$uniqTeam]['scores'][] = $score;
        }

        // sort every persons in teams
        foreach ($teams as $uniqTeam => $t) {
            $time = 0;
            $time68 = 0;

            usort($t['scores'], function($a, $b) {
                if ($a['time'] == $b['time']) return 0;
                elseif ($a['time'] > $b['time']) return 1;
                else return -1;
            });

            if (count($t['scores']) < $competition['score']) {

                $teams[$uniqTeam]['time'] = FSS::INVALID;
                $teams[$uniqTeam]['time68'] = FSS::INVALID;

                continue;
            }

            for($i = 0; $i < $competition['score']; $i++) {
                if ($t['scores'][$i]['time'] == FSS::INVALID) {
                    $teams[$uniqTeam]['time'] = FSS::INVALID;
                    $teams[$uniqTeam]['time68'] = FSS::INVALID;
                    continue 2;
                }
                $time += $t['scores'][$i]['time'];
            }

            if (count($t['scores']) < 6) {
                    $teams[$uniqTeam]['time68'] = FSS::INVALID;
            } else {
                for($i = 0; $i < 6; $i++) {
                    if ($t['scores'][$i]['time'] == FSS::INVALID) {
                        $teams[$uniqTeam]['time68'] = FSS::INVALID;
                        break;
                    }
                    $time68 += $t['scores'][$i]['time'];
                }

                if ($teams[$uniqTeam]['time68'] == -1) {
                    $teams[$uniqTeam]['time68'] = $time68;
                }
            }
            $teams[$uniqTeam]['time'] = $time;
        }

        // Sortiere Teams nach Zeit
        uasort($teams, function ($a, $b) {
            if ($a['time'] == $b['time']) return 0;
            elseif ($a['time'] > $b['time']) return 1;
            else return -1;
        });

        $team_scores[$key][] = array(
            'competition' => $competition,
            'teams' => $teams,
        );
    }

}



$competitions = $db->getRows("
    SELECT `competition_id`,
        SUM(`single`) AS `single`,
        SUM(`gs`) AS `gs`,
        SUM(`la`) AS `la`,
        SUM(`fs`) AS `fs`
    FROM (
        SELECT `competition_id`,COUNT(*) AS `single`,0 AS `gs`,0 AS `la`,0 AS `fs`
        FROM `scores`
        WHERE `team_id` = '".$team['id']."'
        GROUP BY `competition_id`
    UNION
        SELECT `competition_id`,0 AS `single`,COUNT(*) AS `gs`,0 AS `la`,0 AS `fs`
        FROM `scores_gs`
        WHERE `team_id` = '".$team['id']."'
        GROUP BY `competition_id`
    UNION
        SELECT `competition_id`,0 AS `single`,0 AS `gs`,COUNT(*) AS `la`,0 AS `fs`
        FROM `scores_la`
        WHERE `team_id` = '".$team['id']."'
        GROUP BY `competition_id`
    UNION
        SELECT `competition_id`,0 AS `single`,0 AS `gs`,0 AS `la`,COUNT(*) AS `fs`
        FROM `scores_fs`
        WHERE `team_id` = '".$team['id']."'
        GROUP BY `competition_id`
    ) `i`
    GROUP BY `competition_id`
");


// Team - Logo
if ($team['logo']) {
    echo '<p style="float:left;margin-right:20px;"><img src="/'.$config['logo-path'].$team['logo'].'" alt="'.htmlspecialchars($team['short']).'"/></p>';
}

// Verteilung Geschlechter
echo '<p style="float:right;margin-right:20px;">'.Chart::img('team_sex', array($team['id'])).'</p>';

// Kein Team Logo
if (empty($team['logo'])) {
    echo '<p style="margin-right:80px;border:3px solid #FF7D6E; background:#FFD8D3; padding:2px;float:right">Es fehlt noch ein Logo für dieses Team.<br/>Sende doch ein Logo zu!<a class="helpinfo" data-file="logosenden">&nbsp;</a></p>';
}

Title::set(htmlspecialchars($team['name']));
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


echo '<h2>Wettkämpfer</h2>';
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

echo '<h2>Wettkämpfe</h2>';

echo
  '<table class="datatable datatable-sort-competitions"><thead><tr>',
    '<th style="width:13%">Datum</th>',
    '<th style="width:22%">Typ</th>',
    '<th style="width:22%">Ort</th>',
    '<th style="width:8%">Einzel</th>',
    '<th style="width:8%">GS</th>',
    '<th style="width:8%">LA</th>',
    '<th style="width:8%">FS</th>',
    '<th style="width:10%"></th>',
  '</tr></thead>',
  '<tbody>';
foreach ($competitions as $competition) {
    $full = FSS::competition($competition['competition_id']);
    echo
        '<tr>',
          '<td>',$full['date'],'</td>',
          '<td>',htmlspecialchars($full['event']),'</td>',
          '<td>',htmlspecialchars($full['place']),'</td>',
          '<td>',$competition['single'],'</td>',
          '<td>',$competition['gs'],'</td>',
          '<td>',$competition['la'],'</td>',
          '<td>',$competition['fs'],'</td>',
          '<td>'.Link::competition($full['id']).'</td>',
        '</tr>';
}
echo '</tbody></table>';

// Mannschaftswertung
foreach ($team_scores as $fullKey => $tscores) {
    if (!count($tscores)) continue;

    $keys = explode('-', $fullKey);
    $key = $keys[0];
    $sex = false;
    if (count($keys) > 1) {
        $sex = $keys[1];
    }

    echo '<h2 id="dis-'.$fullKey.'-mannschaft">',FSS::dis2name($key);
    if ($sex) echo ' '.FSS::sex($sex);
    echo ' - Mannschaftswertung</h2>';



    $all = array(
        '2' => array(),
        '4' => array(),
        '6' => array(),
    );
    $best68 = PHP_INT_MAX;

    foreach ($tscores as $tscore) {
        $competition = $tscore['competition'];
        if (!isset($all[$competition['score']])) $all[$competition['score']] = array();

        foreach ($tscore['teams'] as $t) {
            if (FSS::isInvalid($t['time'])) continue;
            $all[$competition['score']][] = $t['time'];

            if (!FSS::isInvalid($t['time68']) && $t['time68'] < $best68) $best68 = $t['time68'];
        }
    }

    echo  '<table class="chart-table">';

    foreach ($all as $score => $b) {
        if (!count($b)) continue;
        echo '<tr><th colspan="2">'.$score.' Wertungen ('.count($b).' Zeiten)</th></td></tr>';
        echo '<tr><th>Bestzeit:</th><td>',FSS::time(min($b)),'</td></tr>';
        echo '<tr><th>Durchschnitt:</th><td>',FSS::time(array_sum($b)/count($b)),'</td></tr>';
    }
    if ($best68 != PHP_INT_MAX) echo '<tr><th>Bei 6 Läufern:</th><td>',FSS::time($best68),'</td></tr>';

    echo '</table>';
    echo '<p class="chart">'.Chart::img('team_scores_team', array($_id, $fullKey)).'</p>';





    echo
      '<table class="datatable datatable-sort-team-scores"><thead><tr>',
        '<th style="width:10%">Event</th>',
        '<th style="width:5%">Zeit</th>',
        '<th style="width:5%">bei 6</th>',
        '<th style="width:40%">Wertung</th>',
        '<th style="width:36%">Außerhalb</th>',
        '<th style="width:4%"></th>',
      '</tr></thead>',
      '<tbody>';

    foreach ($tscores as $tscore) {
        $competition = $tscore['competition'];
        foreach ($tscore['teams'] as $t) {
            echo '<tr>';
            echo '<td>'.$competition['date'].'<br/>'.Link::competition($competition['id'], $competition['event'], $competition['place']).'</td>';
            echo '<td>'.FSS::time($t['time']).'</td>';
            echo '<td>'.FSS::time($t['time68']).'</td>';

            $inScore = array();
            $outScore = array();
            $i = 0;
            foreach ($t['scores'] as $score) {
                $link = Link::person($score['person_id'], 'sub', $score['name'], $score['firstname'], FSS::time($score['time']));
                if ($i < $competition['score']) $inScore[] = $link;
                else $outScore[] = $link;
                $i++;
            }

            echo '<td style="font-size:0.7em">'.implode(', ', $inScore).'</td>';
            echo '<td style="font-size:0.7em">'.implode(', ', $outScore).'</td>';
            echo '<td>',$competition['score'],'</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table>';
}



if (count($sc_gs)) {
    echo '<h2 id="toc-sc_gs">Gruppenstafette</h2>';


    $sum = 0;
    $best = PHP_INT_MAX;
    $bad  = 0;
    $i = 0;
    foreach ($sc_gs as $score) {

        if (FSS::isInvalid($score['time'])) continue;

        $sum += $score['time'];
        $i++;

        if ($best > $score['time']) {
            $best = $score['time'];
        }
        if ($bad < $score['time']) {
            $bad = $score['time'];
        }
    }

    echo  '<table class="chart-table">';

    if ($i > 0) echo '<tr><th>Bestzeit:</th><td>',FSS::time($best),'</td></tr>',
          '<tr><th>Schlechteste Zeit:</th><td>',FSS::time($bad),'</td></tr>';

    echo
            '<tr><th>Zeiten:</th><td>',count($sc_gs),'</td></tr>';
    if ($i > 0) echo '<tr><th>Durchschnitt:</th><td>',FSS::time($sum/$i),'</td></tr>';

    echo        '<tr><td style="text-align:center;" colspan="2">'.Chart::img('team_scores_bad_good', array($_id, 'gs')).'</td></tr>',
          '</table>';
    if ($i > 0) echo '<p class="chart">'.Chart::img('team_scores', array($_id, 'gs')).'</p>';


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
            '<td>'.FSS::teamNumber($score['team_number'], $score['c_id'], $id, 'team').'</td>',
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


        $sum = 0;
        $bestFeuer = PHP_INT_MAX;
        $bestAbstellen = PHP_INT_MAX;
        $i = 0;
        foreach ($content['scores'] as $score) {
            if (FSS::isInvalid($score['time'])) continue;
            $sum += $score['time'];
            if ($score['type'] == 'feuer' && $score['time'] < $bestFeuer) $bestFeuer = $score['time'];
            if ($score['type'] == 'abstellen' && $score['time'] < $bestAbstellen) $bestAbstellen = $score['time'];

            $i++;
        }
        $ave = $sum/$i;

        echo  '<table class="chart-table">';

        if ($bestAbstellen != PHP_INT_MAX) echo '<tr><th>Bestzeit (Abstellen):</th><td>',FSS::time($bestAbstellen),'</td></tr>';
        if ($bestFeuer != PHP_INT_MAX) echo '<tr><th>Bestzeit (Feuer):</th><td>',FSS::time($bestFeuer),'</td></tr>';

        echo
                '<tr><th>Zeiten:</th><td>',count($content['scores']),'</td></tr>',
                '<tr><th>Durchschnitt:</th><td>',FSS::time($ave),'</td></tr>',
                '<tr><td style="text-align:center;" colspan="2">'.Chart::img('team_scores_bad_good', array($_id, 'fs-'.$sex)).'</td></tr>',
              '</table>';
        echo '<p class="chart">'.Chart::img('team_scores', array($_id, 'fs-'.$sex)).'</p>';



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
                '<td style="font-size:0.8em;">'.FSS::teamNumber($score['team_number'], $score['c_id'], $id, 'team').'</td>',
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


        $sum = 0;
        $best2005 = PHP_INT_MAX;
        $best2012 = PHP_INT_MAX;
        $bestCTIF = PHP_INT_MAX;
        $bestISFFR = PHP_INT_MAX;
        $i = 0;
        foreach ($content['scores'] as $score) {
            if (FSS::isInvalid($score['time'])) continue;
            $sum += $score['time'];
            if ($score['type'] == 'wko2005' && $score['time'] < $best2005) $best2005 = $score['time'];
            if ($score['type'] == 'wko2012' && $score['time'] < $best2012) $best2012 = $score['time'];
            if ($score['type'] == 'CTIF' && $score['time'] < $bestCTIF) $bestCTIF = $score['time'];
            if ($score['type'] == 'ISFFR' && $score['time'] < $bestISFFR) $bestISFFR = $score['time'];

            $i++;
        }
        $ave = $sum/$i;

        echo  '<table class="chart-table">';

        if ($best2005 != PHP_INT_MAX) echo '<tr><th>Bestzeit (WKO 2005):</th><td>',FSS::time($best2005),'</td></tr>';
        if ($best2012 != PHP_INT_MAX) echo '<tr><th>Bestzeit (WKO 2012):</th><td>',FSS::time($best2012),'</td></tr>';
        if ($bestCTIF != PHP_INT_MAX) echo '<tr><th>Bestzeit (CTIF):</th><td>',FSS::time($bestCTIF),'</td></tr>';
        if ($bestISFFR != PHP_INT_MAX) echo '<tr><th>Bestzeit (ISFFR):</th><td>',FSS::time($bestISFFR),'</td></tr>';

        echo
                '<tr><th>Zeiten:</th><td>',count($content['scores']),'</td></tr>',
                '<tr><th>Durchschnitt:</th><td>',FSS::time($ave),'</td></tr>',
                '<tr><td style="text-align:center;" colspan="2">'.Chart::img('team_scores_bad_good', array($_id, 'la-'.$sex)).'</td></tr>',
              '</table>';
        echo '<p class="chart">'.Chart::img('team_scores', array($_id, 'la-'.$sex)).'</p>';


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
                '<td style="font-size:0.7em;">'.FSS::teamNumber($score['team_number'], $score['c_id'], $id, 'team').'</td>',
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


echo '<h2 id="map">Karte</h2>';
if (Map::isFile('teams', $_id)) {
    echo '<div class="nine columns staticmap">'.Map::getImg('teams', $_id).'</div>';
    echo '<div class="four columns staticmap"><button id="loadmap">Interaktive Karte laden</button></div>';
} else {
    echo '<div class="nine columns staticmap"><img src="/styling/images/no-location.png" alt=""/><br/>Keine Kartenposition vorhanden</div>';
    echo '<div class="four columns staticmap"><button id="loadmap2">Interaktive Karte zum Bearbeiten laden</button></div>';
}


echo '
<h2 id="fehler">Fehler melden</h2>
<p>Beim Importieren der Ergebnisse kann es immer wieder mal zu Fehlern kommen. Geraden wenn die Namen in den Ergebnislisten verkehrt geschrieben wurden, kann keine eindeutige Zuordnung stattfinden. Außerdem treten auch Probleme mit Umlauten oder anderen besonderen Buchstaben im Namen auf.</p>
<p>Ihr könnt jetzt beim Korrigieren der Daten helfen. Dafür klickt ihr auf folgenden Link und generiert eine Meldung für den Administrator. Dieser überprüft dann die Eingaben und leitet weitere Schritte ein.</p>
<p><button id="report-error">Fehler mit diesem Team melden</button></p>
';
