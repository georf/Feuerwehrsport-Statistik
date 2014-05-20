<?php

// a == id
// b == key

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (Check::get('b')) $_GET['key'] = $_GET['b'];

if (!Check::get('id', 'key')) throw new Exception('not enough arguments');
if (!Check::isIn($_GET['id'], 'persons')) throw new Exception('bad person');
$id = intval($_GET['id']);
$key = $_GET['key'];

$person = FSS::tableRow('persons', $id);

switch ($key) {

    case 'hl':
        $scores = $db->getRows("
            SELECT *
            FROM (
                SELECT *
                FROM (
                    SELECT `s`.`time`,`c`.`date`,`c`.`id`
                    FROM `scores` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    WHERE `s`.`person_id` = '".$id."'
                    AND `s`.`discipline` = 'HL'
                    AND `s`.`time` IS NOT NULL
                    ORDER BY `s`.`time`
                ) `i`
                GROUP BY `i`.`id`
            ) `i2`
            ORDER BY `i2`.`date`
        ");
        $title = FSS::dis2name($key);
        break;

    case 'hb':
        $scores = $db->getRows("
            SELECT *
            FROM (
                SELECT *
                FROM (
                    SELECT `s`.`time`,`c`.`date`,`c`.`id`
                    FROM `scores` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    WHERE `s`.`person_id` = '".$id."'
                    AND `s`.`discipline` = 'HB'
                    AND `s`.`time` IS NOT NULL
                    ORDER BY `s`.`time`
                ) `i`
                GROUP BY `i`.`id`
            ) `i2`
            ORDER BY `i2`.`date`
        ");
        $title = FSS::dis2name($key);
        break;

    case 'zk':
        $scores = $db->getRows("
            SELECT
                `c`.`date`,
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
            GROUP BY `c`.`id`
            ORDER BY `date`
        ");
        $title = FSS::dis2name($key);
        break;


    case 'gs':
        $scores = $db->getRows("
            SELECT `s`.`time`,`c`.`date`
            FROM `scores_gs` `s`
            INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
            INNER JOIN `person_participations_gs` `p` ON `p`.`score_id` = `s`.`id`
            WHERE `s`.`time` IS NOT NULL AND `p`.`person_id` = '".$id."'
            ORDER BY `c`.`date`
        ");
        $title = FSS::dis2name($key);
        break;

    case 'fs':
        $scores = $db->getRows("
            SELECT `s`.`time`,`c`.`date`
            FROM `scores_fs` `s`
            INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
            INNER JOIN `person_participations_fs` `p` ON `p`.`score_id` = `s`.`id`
            WHERE `s`.`time` IS NOT NULL AND `p`.`person_id` = '".$id."'
            ORDER BY `c`.`date`
        ");
        $title = FSS::dis2name($key);
        break;

    case 'la':
        $scores = $db->getRows("
            SELECT `s`.`time`,`c`.`date`
            FROM `scores_la` `s`
            INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
            INNER JOIN `person_participations_la` `p` ON `p`.`score_id` = `s`.`id`
            WHERE `s`.`time` IS NOT NULL AND `p`.`person_id` = '".$id."'
            ORDER BY `c`.`date`
        ");
        $title = FSS::dis2name($key);
        break;

    default:
        throw new Exception('bad key');
        break;
}

$times = array();
$labels = array();
$hb = array();
$hl = array();

foreach ($scores as $score) {
    $times[] = intval($score['time'])/100;
    $labels[] = gDate($score['date']);

    if ($key == 'zk') {
        $hb[] = intval($score['hb'])/100;
        $hl[] = intval($score['hl'])/100;
    }
}

$MyData = new pData();
$MyData->addPoints($times, 'time');
$MyData->setSerieDescription('time', $title);
$MyData->addPoints($labels, "Daten");
$MyData->setAbscissa("Daten");

if ($key == 'zk') {
    $MyData->addPoints($hb, 'HB');
    $MyData->addPoints($hl, 'HL');
}

$w = 700;
$h = 280;
$title = $person['firstname'].' '.$person['name'];

/* Create the pChart object */
$myPicture = Chart::create($w, $h, $MyData);

/* Turn of Antialiasing */
$myPicture->Antialias = FALSE;

/* Draw the background #9FC5EE */
$Settings = array("R"=>169, "G"=>217, "B"=>238);
$myPicture->drawFilledRectangle(0, 0, Chart::size($w), Chart::size($h), $Settings);

$Settings = array(
  "StartR"=>159, "StartG"=>197, "StartB"=>238,
  "EndR"=>133, "EndG"=>184, "EndB"=>238,
  "Alpha"=>80
);
$myPicture->drawGradientArea(0, 0, Chart::size($w), Chart::size(20), DIRECTION_VERTICAL, $Settings);

/* Add a border to the picture #87A8CC*/
$myPicture->drawRectangle(0, 0, Chart::size($w-1), Chart::size($h-1),array("R"=>135, "G"=>168, "B"=>204));

/* Write the chart title */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/calibri.ttf","FontSize"=>8,"R"=>255,"G"=>255,"B"=>255));
$myPicture->drawText(Chart::size(10), Chart::size(18), $title,array("FontSize"=>Chart::size(11),"Align"=>TEXT_ALIGN_BOTTOMLEFT));

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>Chart::size(7),"R"=>0,"G"=>0,"B"=>0));

/* Define the chart area */
$myPicture->setGraphArea(Chart::size(25),Chart::size(25),Chart::size(682),Chart::size(215));

/* Draw the scale */
$scaleSettings = array(
  "XMargin"=>10,
  "YMargin"=>10,
  "Floating"=>TRUE,
  "GridR"=>200,
  "GridG"=>200,
  "GridB"=>200,
  "DrawSubTicks"=>TRUE,
  "CycleBackground"=>TRUE,
  "LabelRotation" => 90
);
$myPicture->drawScale($scaleSettings);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the line chart */
$myPicture->drawLineChart();
$myPicture->drawPlotChart(array("PlotSize"=>Chart::size(1),"DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>Chart::size(1),"Surrounding"=>-50,"BorderAlpha"=>80));

/* Write the chart legend */
$myPicture->drawLegend(Chart::size(500),Chart::size(10),array(
  "Style"=>LEGEND_NOBORDER,
  "Mode"=>LEGEND_HORIZONTAL,
  "FontR"=>255,"FontG"=>255,"FontB"=>255,
  "FontName"=>PCHARTDIR."fonts/calibri.ttf",
  "FontSize"=>Chart::size(10)
));


/* Draw the standard mean and the geometric one */
$Mean = $MyData->getSerieAverage('time');
$myPicture->drawThreshold($Mean,array("WriteCaption"=>TRUE,"Caption"=>"Durchscnnitt","CaptionAlign"=>CAPTION_RIGHT_BOTTOM));

/* Render the picture */
$myPicture->stroke();
