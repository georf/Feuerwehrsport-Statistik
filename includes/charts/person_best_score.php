<?php

// a = id
// b = key

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (Check::get('b')) $_GET['key'] = $_GET['b'];

if (!Check::get('id', 'key')) throw new Exception('not enough arguments');
if (!Check::isIn($_GET['id'], 'persons')) throw new Exception('bad person');
if (!in_array($_GET['key'], array('hl', 'hb'))) throw new Exception('bad key');

$id = intval($_GET['id']);

$person = FSS::tableRow('persons', $_GET['id']);

$scores = array();
$title  = '';
/*
    case 'zk':

        $scores = $db->getRows("
            SELECT
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
            GROUP BY `hl`.`person_id`
            ORDER BY `time`
        ");
        $title = FSS::dis2name($key);*/


TempDB::generate('x_scores_'.$person['sex']);
$scores = $db->getRows("
    SELECT MIN( `time` ) AS `time`, `person_id`
    FROM `x_scores_".$person['sex']."`
    WHERE `time` IS NOT NULL
    AND `discipline` LIKE '".$db->escape($_GET['key'])."'
    GROUP BY `person_id`
    ORDER BY `time`
");

$points = array();
$your = array();
$labels = array();
$i = 1;
foreach ($scores as $score) {
    $points[] = intval($score['time'])/100;

    if ($score['person_id'] == $person['id']) {
        $your[] = intval($score['time'])/100;
    } else {
        $your[] = VOID;
    }

    $labels[] = $i.'.';
    $i++;
}

$MyData = new pData();
$MyData->addPoints($points, "time");
$MyData->addPoints($your, "your");


$MyData->addPoints($labels, "Platzierung");
$MyData->setXAxisName("Platzierung der Bestzeiten aller Wettkämpfer");
$MyData->setAbscissa('Platzierung');

$MyData->setSerieDescription("time", 'Alle Bestzeiten');
$MyData->setSerieDescription("your", $person['firstname'].' '.$person['name']);

$MyData->setAxisName(0, "Zeit in Sekunden");

$w = 700;
$h = 230;
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
$myPicture->setGraphArea(Chart::size(40),Chart::size(30),Chart::size(660),Chart::size(200));

/* Draw the scale */
$scaleSettings = array(
  "XMargin"=>Chart::size(10),
  "YMargin"=>Chart::size(10),
  "Floating"=>TRUE,
  "GridR"=>200,
  "GridG"=>200,
  "GridB"=>200,
  "DrawSubTicks"=>false,
  "CycleBackground"=>TRUE,
  "LabelSkip"=>49
);
$myPicture->drawScale($scaleSettings);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the line chart */

$MyData->setSerieDrawable('your', false);
$myPicture->drawLineChart();

$MyData->setSerieDrawable('your', true);
$MyData->setSerieDrawable('time', false);
$myPicture->drawPlotChart(array("PlotSize"=>Chart::size(1),"DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>Chart::size(1),"Surrounding"=>-50,"BorderAlpha"=>80));

$MyData->setSerieDrawable('time', true);
/* Write the chart legend */
$myPicture->drawLegend(Chart::size(500),Chart::size(10),array(
  "Style"=>LEGEND_NOBORDER,
  "Mode"=>LEGEND_HORIZONTAL,
  "FontR"=>255,"FontG"=>255,"FontB"=>255,
  "FontName"=>PCHARTDIR."fonts/calibri.ttf",
  "FontSize"=>Chart::size(10)
));

$myPicture->drawText(Chart::size(350), Chart::size(220), 'Platzierung der Bestzeiten aller Wettkämpfer', array('Align'=>TEXT_ALIGN_MIDDLEMIDDLE));


/* Draw the standard mean and the geometric one */
$Mean = $MyData->getSerieAverage("time");
$myPicture->drawThreshold($Mean,array("WriteCaption"=>TRUE,"Caption"=>"Durchschnnitt","CaptionAlign"=>CAPTION_RIGHT_BOTTOM));

/* Render the picture */
$myPicture->stroke();

/* Render the picture */
$myPicture->stroke();
