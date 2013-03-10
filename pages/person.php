<?php

if (!isset($_GET['id']) || !Check::isIn($_GET['id'], 'persons')) throw new PageNotFound();
$_id = $_GET['id'];


$person = $db->getFirstRow("
    SELECT *
    FROM `persons`
    WHERE `id` = '".$db->escape($_id)."'
");

$id = $person['id'];

echo dataDiv($person, 'person');


$teams = $db->getRows("
    SELECT `t`.*, COUNT(`i`.`key`) AS `count`,
        0 AS 'hb',0 AS 'hl',0 AS 'gs',0 AS 'fs',0 AS 'la'
    FROM (
            SELECT `team_id`,CONCAT('HB',`id`) AS `key`
            FROM `scores`
            WHERE `person_id` = '".$id."'
            AND `discipline` = 'HB'
        UNION
            SELECT `team_id`,CONCAT('HL',`id`) AS `key`
            FROM `scores`
            WHERE `person_id` = '".$id."'
            AND `discipline` = 'HL'
        UNION
            SELECT `team_id`,CONCAT('GS',`id`) AS `key`
            FROM `scores_gruppenstafette`
            WHERE `person_1` = '".$id."'
            OR `person_2` = '".$id."'
            OR `person_3` = '".$id."'
            OR `person_4` = '".$id."'
            OR `person_5` = '".$id."'
            OR `person_6` = '".$id."'
        UNION
            SELECT `team_id`,CONCAT('LA',`id`) AS `key`
            FROM `scores_loeschangriff`
            WHERE `person_1` = '".$id."'
            OR `person_2` = '".$id."'
            OR `person_3` = '".$id."'
            OR `person_4` = '".$id."'
            OR `person_5` = '".$id."'
            OR `person_6` = '".$id."'
            OR `person_7` = '".$id."'
        UNION
            SELECT `team_id`,CONCAT('FS',`id`) AS `key`
            FROM `scores_stafette`
            WHERE `person_1` = '".$id."'
            OR `person_2` = '".$id."'
            OR `person_3` = '".$id."'
            OR `person_4` = '".$id."'
    ) `i`
    INNER JOIN `teams` `t` ON `t`.`id` = `i`.`team_id`
    GROUP BY `team_id`
");


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

$zk = $db->getRows("
    SELECT
        `c`.`place_id`,`p`.`name` AS `place`,
        `c`.`event_id`,`e`.`name` AS `event`,
        `c`.`score_type_id`,
        `hb`.`competition_id`,`c`.`date`,
        `hb`.`time` AS `hb`,
        `hl`.`time` AS `hl`,
        `hb`.`time` + `hl`.`time` AS `time`
    FROM (
        SELECT `time`,`competition_id`
        FROM `scores`
        WHERE `person_id` = '".$id."'
        AND `discipline` = 'HB'
        AND `time` IS NOT NULL
        ORDER BY `time`
    ) `hb`
    INNER JOIN (
        SELECT `time`,`competition_id`
        FROM `scores`
        WHERE `person_id` = '".$id."'
        AND `discipline` = 'HL'
        AND `time` IS NOT NULL
        ORDER BY `time`
    ) `hl` ON `hl`.`competition_id` = `hb`.`competition_id`
    INNER JOIN `competitions` `c` ON `c`.`id` = `hb`.`competition_id`
    INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
    INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
    GROUP BY `c`.`id`
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
    FROM `scores_gruppenstafette` `s`
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
    FROM `scores_loeschangriff` `s`
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
    FROM `scores_stafette` `s`
    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
    INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
    INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
    WHERE `person_1` = '".$id."'
    OR `person_2` = '".$id."'
    OR `person_3` = '".$id."'
    OR `person_4` = '".$id."'
    ORDER BY `date` DESC
");


$disEinzel = array(
    'hl' => $hl,
    'hb' => $hb,
    'zk' => $zk,
);

$disGruppe = array(
    'gs' => $gs,
    'la' => $la,
    'fs' => $fs,
);


Title::set(htmlspecialchars($person['firstname']).' '.htmlspecialchars($person['name']));
echo '<h1>',htmlspecialchars($person['firstname']),' ',htmlspecialchars($person['name']),'</h1>';
echo '<div class="sixteen columns clearfix">';


echo '<div class="four columns">',
    '<div class="toc">',
        '<h5>Inhaltsverzeichnis</h5>',
        '<ol>';

foreach (array_merge($disEinzel, $disGruppe) as $key => $scores) {
    if (count($scores) > 0) {
        $name = FSS::dis2name($key);
        echo '<li><a href="#dis-',FSS::name2id($name),'">',$name,'</a></li>';
    }
}

echo        '<li><a href="#team">Mannschaft</a></li>',
            '<li><a href="#fehler">Fehler melden</a></li>',
        '</ol>',
    '</div></div>';

echo '<div class="four columns"><img src="chart.php?type=person_overview&amp;id='.$_id.'" alt="" class="infochart" data-file="person_overview"/></div>';


if (count($teams)) {
    $t = array();

    echo '<div class="seven columns team-corner"><h5>Mannschaft';
    if (count($teams) > 1) echo 'en';
    echo '</h5><ul>';

    foreach ($teams as $team) {

        echo '<li class="team" title="'.htmlspecialchars($team['name']).'">';
        if ($team['logo']) {
            echo '<img src="'.$config['logo-path'].$team['logo'].'" alt="'.htmlspecialchars($team['short']).'"/>';
        } else {
            echo '<p>'.htmlspecialchars($team['short']).'</p>';
        }
        echo '</li>';

        $t[$team['id']] = $team;
    }
    $teams = $t;

    echo '</ul></div>';
}

echo '</div>';

foreach (array_merge($disEinzel, $disGruppe) as $key => $scores) {

    // Nur Disziplinen anzeigen, die auch Zeiten haben
    if (count($scores) === 0) continue;

    $name = FSS::dis2name($key);

    $sum  = 0;
    $i    = 0;
    $best = PHP_INT_MAX;
    $bad  = 0;

    foreach ($scores as $score) {
        // Zählen für Team
        if (isset($score['team_id']) && $score['team_id']) {
            $teams[$score['team_id']][$key]++;
        }

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

    echo '<div class="competition-box">';
    echo '<h2 style="clear:both; margin-top:40px;" id="dis-',FSS::name2id($name),'">'.$name.'</h2>';

    echo  '<table class="chart-table">',
          '<tr><th>Bestzeit:</th><td>',FSS::time($best),'</td></tr>',
          '<tr><th>Schlechteste Zeit:</th><td>',FSS::time($bad),'</td></tr>',
          '<tr><th>Zeiten:</th><td>',count($scores),'</td></tr>';

    if ($i > 0) echo '<tr><th>Durchschnitt:</th><td>',FSS::time($sum/$i),'</td></tr>';

    echo
          '<tr><td colspan="2">Die Ergebnisse beziehen sich nur auf die hier gespeicherten Daten.</td></tr>';

    if ($key != 'zk') echo '<tr><td colspan="2" style="text-align:center;"><img class="big" src="chart.php?type=person_bad_good&amp;key=',$key,'&amp;id=',$_id,'" alt=""/></td></tr>';

    echo
          '</table>';
    echo '<p class="chart"><img class="big" src="chart.php?type=person&amp;key=',$key,'&amp;id=',$_id,'" style="width:700px;height:280px" alt=""/></p>';



    if (in_array($key, array('hl', 'hb'))) {


        echo
          '<table class="datatable sc_'.$key.'"><thead><tr>',
            '<th style="width:16%">Wettkampf</th>',
            '<th style="width:25%">Ort</th>',
            '<th style="width:31%">Mannschaft</th>',
            '<th style="width:10%">Datum</th>',
            '<th style="width:10%">Zeit</th>',
            '<th style="width:8%"></th>',
          '</tr></thead><tbody>';
        foreach ($scores as $score) {

            echo
            '<tr data-id="',$score['score_id'],'">',
              '<td>'.Link::event($score['event_id'], $score['event']).'</td>',
              '<td>'.Link::place($score['place_id'], $score['place']).'</td>',
              '<td class="team">';

            if ($score['team_id']) {

                $t_name = $teams[$score['team_id']]['name'];
                if ($score['score_type_id']) {
                    $t_name .= ' '.FSS::teamNumber($score['team_number']);
                }
                echo Link::team($score['team_id'], $t_name);
            }
            echo '</td>',
              '<td>',$score['date'],'</td>',
              '<td class="number">',FSS::time($score['time']),'</td>',
              '<td>'.Link::competition($score['competition_id'],'Details').'</td>',
              '</tr>';
        }
        echo '</tbody></table>';


    } elseif ($key == 'zk') {


        echo
          '<table class="datatable sc_'.$key.'"><thead><tr>',
            '<th style="width:16%">Wettkampf</th>',
            '<th style="width:25%">Ort</th>',
            '<th style="width:10%">Datum</th>',
            '<th style="width:12%">HB</th>',
            '<th style="width:12%">HL</th>',
            '<th style="width:12%">Zeit</th>',
            '<th style="width:8%"></th>',
          '</tr></thead><tbody>';
        foreach ($scores as $score) {

            echo
            '<tr data-scoreid="',$score['score_id'],'">',
              '<td>'.Link::event($score['event_id'], $score['event']).'</td>',
              '<td>'.Link::place($score['place_id'], $score['place']).'</td>',
              '<td>',$score['date'],'</td>',
              '<td>',FSS::time($score['hb']),'</td>',
              '<td>',FSS::time($score['hl']),'</td>',
              '<td>',FSS::time($score['time']),'</td>',
              '<td>'.Link::competition($score['competition_id'],'Details').'</td>',
              '</tr>';
        }
        echo '</tbody></table>';


    } else {


        echo '<table class="datatable sc_'.$key.'"><thead><tr>',
            '<th style="width:13%">Wettkampf</th>',
            '<th style="width:17%">Ort</th>',
            '<th style="width:28%">Mannschaft</th>',
            '<th style="width:10%">Datum</th>',
            '<th style="width:10%">Zeit</th>',
            '<th style="width:14%">Position</th>',
            '<th style="width:8%"></th>',
          '</tr></thead><tbody>';
        foreach ($scores as $score) {

            echo
            '<tr data-scoreid="',$score['score_id'],'">',
              '<td>'.Link::event($score['event_id'], $score['event']).'</td>',
              '<td>'.Link::place($score['place_id'], $score['place']).'</td>',
              '<td class="team">';

            if ($score['team_id']) {

                $t_name = $teams[$score['team_id']]['name'];
                if ($score['score_type_id']) {
                    $t_name .= ' '.FSS::teamNumber($score['team_number']);
                }
                echo Link::team($score['team_id'], $t_name);
            }
            echo '</td>',
              '<td>',$score['date'],'</td>',
              '<td class="timecol">',FSS::time($score['time']),'</td>';


            for ($wk = 1; $wk < 8; $wk++) {
                if (array_key_exists('person_'.$wk, $score) && $score['person_'.$wk] == $id) {
                    echo '<td>'.WK::type($wk, $person['sex'], $key).'</td>';
                    break;
                }
            }

            echo
              '<td>'.Link::competition($score['competition_id'], 'Details').'</td>',
              '</tr>';
        }
        echo '</tbody></table>';

    }
    echo '</div>';
}

if (count($teams)) {

    echo
      '<h2 id="team" style="clear:both; margin-top:40px;">Mannschaft</h2>',
      '<p>',htmlspecialchars($person['firstname']),' ',htmlspecialchars($person['name']),' trat für folgende Mannschaften an:</p>',

      '<div class="team-listing">';

    foreach ($teams as $team){
        echo '<div class="team">';
        echo '<div class="logo" title="'.htmlspecialchars($team['name']).'">';
        if ($team['logo']) {
            echo '<img src="'.$config['logo-path'].$team['logo'].'" alt="'.htmlspecialchars($team['short']).'"/>';
        } else {
            echo '<p>'.htmlspecialchars($team['short']).'</p>';
        }
        echo '</div><ul class="actions disc">',
                '<li><a href="?page=team&amp;id=',$team['id'],'">Details</a></li>',
            '</ul><div>',
            '<h3>',htmlspecialchars($team['name']),'</h3>',
            '<table>',
                '<tr><th>Gelaufene Zeiten:</th><td>',$team['count'],'</td></tr>'.
                '<tr><th></th><td>';

        $elems = array();
        foreach (array('hl','hb','gs','la','fs') as $key) {
            if ($team[$key] > 0) {
                $elems[] = $team[$key].'x '.FSS::dis2name($key);
            }
        }
        echo implode(', ', $elems);

        echo '</td></tr>';

        $links = $db->getRows("
          SELECT *
          FROM `links`
          WHERE `for_id` = '".$team['id']."'
          AND `for` = 'team'
        ");

        if (count($links)) {
            echo '<tr><th>Webseite:</th><td>';

            $l = array();
            foreach ($links as $link) {
                $l[] = '<a href="'.htmlspecialchars($link['url']).'">'.htmlspecialchars($link['name']).'</a>';
            }
            echo implode('<br/>', $l);

            echo '</td></tr>';
        }

        echo
            '</table>',
        '</div>';
        echo '</div>';
    }
    echo '</div>';
}


echo '<h2 id="fehler">Fehler melden</h2>
        <p>Beim Importieren der Ergebnisse kann es immer wieder mal zu Fehlern kommen. Geraden wenn die Namen in den Ergebnislisten verkehrt geschrieben wurden, kann keine eindeutige Zuordnung stattfinden. Außerdem treten auch Probleme mit Umlauten oder anderen besonderen Buchstaben im Namen auf.</p>
        <p>Ihr könnt jetzt beim Korrigieren der Daten helfen. Dafür klickt ihr auf folgenden Link und generiert eine Meldung für den Administrator. Dieser überprüft dann die Eingaben und leitet weitere Schritte ein.</p>
        <p><button id="report-error">Fehler mit dieser Person melden</button></p>';
