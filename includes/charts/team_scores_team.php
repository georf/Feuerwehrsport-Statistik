<?php

// a = id
// b = key

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (Check::get('b')) $_GET['key'] = $_GET['b'];

if (!Check::get('id', 'key')) throw new Exception('not enough arguments');
if (!Check::isIn($_GET['id'], 'teams')) throw new Exception('bad team');
$id = intval($_GET['id']);

$keys = explode('-', $_GET['key']);
$key = $keys[0];
$sex = false;
if (count($keys) > 1) {
    $sex = $keys[1];
}

if ($sex && !in_array($sex, array('male', 'female'))) throw new Exception('bad sex');

$scores = array();
$title  = '';


TempDB::generate('x_full_competitions');

$competitions = $db->getRows("
    SELECT *
    FROM `x_full_competitions`
    WHERE `score_type_id` IS NOT NULL
    ORDER BY `date`
");
$competitionScores = array();

foreach ($competitions as $c_id => $competition) {
    switch ($key) {
        case 'hb':
            if (!$sex) throw new Exception('sex not defined');
            TempDB::generate('x_scores_'.$sex);

            $scores = $db->getRows("
                SELECT `best`.*
                FROM (
                    SELECT *
                    FROM (
                        (
                            SELECT `id`,`team_number`,
                            `person_id`,
                            `time`
                            FROM `x_scores_".$sex."`
                            WHERE `time` IS NOT NULL
                            AND `competition_id` = '".$competition['id']."'
                            AND `discipline` = 'HB'
                            AND `team_number` != -2
                            AND `team_id` = '".$id."'
                        ) UNION (
                            SELECT `id`,`team_number`,
                            `person_id`,
                            ".FSS::INVALID." AS `time`
                            FROM `x_scores_".$sex."`
                            WHERE `time` IS NULL
                            AND `competition_id` = '".$competition['id']."'
                            AND `discipline` = 'HB'
                            AND `team_number` != -2
                            AND `team_id` = '".$id."'
                        ) ORDER BY `time`
                    ) `all`
                    GROUP BY `person_id`
                ) `best`
                ORDER BY `time`
            ");

            $title = FSS::dis2name($key);
            break;

        case 'hl':
            TempDB::generate('x_scores_male');

            $scores = $db->getRows("
                SELECT `best`.*
                FROM (
                    SELECT *
                    FROM (
                        (
                            SELECT `id`,`team_number`,
                            `person_id`,
                            `time`
                            FROM `x_scores_male`
                            WHERE `time` IS NOT NULL
                            AND `competition_id` = '".$competition['id']."'
                            AND `discipline` = 'HL'
                            AND `team_number` != -2
                            AND `team_id` = '".$id."'
                        ) UNION (
                            SELECT `id`,`team_number`,
                            `person_id`,
                            ".FSS::INVALID." AS `time`
                            FROM `x_scores_male`
                            WHERE `time` IS NULL
                            AND `competition_id` = '".$competition['id']."'
                            AND `discipline` = 'HL'
                            AND `team_number` != -2
                            AND `team_id` = '".$id."'
                        ) ORDER BY `time`
                    ) `all`
                    GROUP BY `person_id`
                ) `best`
                ORDER BY `time`
            ");
            $title = FSS::dis2name($key).' '.FSS::sex($sex);
            break;

        default:
            throw new Exception('bad key');
            break;
    }

    if (!count($scores)) continue;

    // Bereche die Wertung
    $teams = array();
    foreach ($scores as $score) {
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
    usort($teams, function ($a, $b) {
        if ($a['time'] == $b['time']) return 0;
        elseif ($a['time'] > $b['time']) return 1;
        else return -1;
    });

    if (count($teams)) {
        $competitionScores[] = array(
            'competition' => $competition,
            'team' => $teams[0],
        );
    }
}

$points4 = array();
$i4 = false;
$points6 = array();
$i6 = false;
$points68 = array();
$i68 = false;

$labels = array();
foreach ($competitionScores as $score) {
    if (FSS::isInvalid($score['team']['time'])) continue;

    if ($score['competition']['score'] == 4) {
        $points4[] = intval($score['team']['time'])/100;
        $points6[] = VOID;
        $i4 = true;
    } elseif ($score['competition']['score'] == 6) {
        $points4[] = VOID;
        $points6[] = intval($score['team']['time'])/100;
        $i6 = true;
    } else {
        $points4[] = VOID;
        $points6[] = VOID;
    }

    if (!FSS::isInvalid($score['team']['time68']) && $score['competition']['score'] != 6) {
        $points68[] = intval($score['team']['time68'])/100;
        $i68 = true;
    } else {
        $points68[] = VOID;
    }

    $labels[] = gDate($score['competition']['date']);
}

$MyData = new pData();

if ($i4) {
    $MyData->addPoints($points4, "time4");
    $MyData->setPalette("time4", array("R"=>255,"G"=>0,"B"=>0,"Alpha"=>80));
    $MyData->setSerieDescription("time4", '4 Läufer');
}

if ($i6) {
    $MyData->addPoints($points6, "time6");
    $MyData->setPalette("time6", array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>60));
    $MyData->setSerieDescription("time6", '6 Läufer');
}

if ($i68) {
    $MyData->addPoints($points68, "time68");
    $MyData->setPalette("time68", array("R"=>255,"G"=>255,"B"=>0,"Alpha"=>80));
    $MyData->setSerieDescription("time68", '6 Berechnet');
}

if (!count($labels)) {
    throw new Exception("Keine Daten");
}

$MyData->addPoints($labels, "Daten");
$MyData->setAbscissa("Daten");

$w = 700;
$h = 260;
$myPicture = Chart::create($w, $h, $MyData);

/* Turn of Antialiasing */
$myPicture->Antialias = FALSE;

/* Draw the background #9FC5EE */
$myPicture->drawFilledRectangle(0, 0, Chart::size($w), Chart::size($h), array(
    "R" => 169,
    "G" => 217,
    "B" => 238
));

$myPicture->drawGradientArea(0, 0, Chart::size($w), Chart::size(20), DIRECTION_VERTICAL, array(
  "StartR"=>159, "StartG"=>197, "StartB"=>238,
  "EndR"=>133, "EndG"=>184, "EndB"=>238,
  "Alpha"=>80
));

/* Add a border to the picture #87A8CC*/
$myPicture->drawRectangle(0, 0, Chart::size($w-1), Chart::size($h-1), array(
    "R"=>135,
    "G"=>168,
    "B"=>204
));

/* Write the chart title */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/calibri.ttf","FontSize"=>Chart::size(8),"R"=>255,"G"=>255,"B"=>255));
$myPicture->drawText(Chart::size(10), Chart::size(18), $title, array("FontSize"=>Chart::size(11),"Align"=>TEXT_ALIGN_BOTTOMLEFT));

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>Chart::size(7),"R"=>0,"G"=>0,"B"=>0));

/* Define the chart area */
$myPicture->setGraphArea(Chart::size(40),Chart::size(30),Chart::size(660),Chart::size(198));

/* Draw the scale */
$scaleSettings = array(
  "XMargin"=>Chart::size(10),
  "YMargin"=>Chart::size(10),
  "Floating"=>TRUE,
  "GridR"=>200,
  "GridG"=>200,
  "GridB"=>200,
  "DrawSubTicks"=>TRUE,
  "CycleBackground"=>TRUE,
  "LabelRotation"=>90,
  "Mode"=>SCALE_MODE_FLOATING
);
$myPicture->drawScale($scaleSettings);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the line chart */
$myPicture->drawLineChart();
$myPicture->drawPlotChart(array("PlotSize"=>1,"DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-50,"BorderAlpha"=>80));

/* Write the chart legend */
$myPicture->drawLegend(Chart::size(500),Chart::size(10),array(
  "Style"=>LEGEND_NOBORDER,
  "Mode"=>LEGEND_HORIZONTAL,
  "FontR"=>255,"FontG"=>255,"FontB"=>255,
  "FontName"=>PCHARTDIR."fonts/calibri.ttf",
  "FontSize"=>Chart::size(10)
));

/* Render the picture */
$myPicture->stroke();

