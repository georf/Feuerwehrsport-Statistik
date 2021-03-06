<?php

$competitions = $db->getRows("
  SELECT `c`.*,`p`.`name` AS `place`
  FROM `competitions` `c`
  INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
  ORDER BY `c`.`date`;
");

$females = array();
$males   = array();
$teams   = array();

$labels = array();

foreach ($competitions as $competition) {
    $labels[] = mb_substr($competition['place'], 0, 6,'UTF-8').' '.date('y',strtotime($competition['date']));

    $count = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM (
            SELECT `person_id`
            FROM `scores` `s`
            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
            WHERE `s`.`competition_id` = '".$competition['id']."'
            AND `p`.`sex` = 'female'
            GROUP BY `s`.`person_id`
        ) `i`
    ", 'count');
    $females[] = ($count <= 0)? VOID : $count;

    $count = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM (
            SELECT `person_id`
            FROM `scores` `s`
            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
            WHERE `s`.`competition_id` = '".$competition['id']."'
            AND `p`.`sex` = 'male'
            GROUP BY `s`.`person_id`
        ) `i`
    ", 'count');
    $males[] = ($count <= 0)? VOID : $count;

    $count = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM (
            SELECT `team`
            FROM (
                SELECT CONCAT(CAST(`team_id` AS CHAR),`sex`,CAST(`team_number` AS CHAR)) AS `team`

                FROM `group_scores` `gs`
                INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
                INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
                WHERE `gsc`.`competition_id` = '".$competition['id']."'
            UNION
                SELECT CONCAT(CAST(`team_id` AS CHAR),`pi`.`sex`,CAST(`team_number` AS CHAR)) AS `team`
                FROM `scores` `si`
                INNER JOIN `persons` `pi` ON `si`.`person_id` = `pi`.`id`
                WHERE `si`.`competition_id` = '".$competition['id']."'
            ) `i`
            GROUP BY `i`.`team`
        ) `i2`
    ", 'count');
    $teams[] = ($count <= 0)? VOID : $count;
}

$MyData = new pData();
$MyData->addPoints($labels, 'Labels');
$MyData->setAbscissa('Labels');

$MyData->addPoints($females, 'weiblich');
$MyData->addPoints($males, 'männlich');
$MyData->addPoints($teams, 'Mannschaften');


$w = 920;
$h = 30 + $MyData->getSerieCount('Labels')*8.2;
$title = 'Anzahl der Zeiten pro Wettkampf';
/* Create the pChart object */
$myPicture = Chart::create($w, $h, $MyData);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Write the chart title */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/DejaVuSerifCondensed.ttf","FontSize"=>Chart::size(8),"R"=>0,"G"=>0,"B"=>0));
$myPicture->drawText(Chart::size(10), Chart::size(18), 'Anzahl der Teilnehmer pro Wettkampf',array("FontSize"=>Chart::size(11),"Align"=>TEXT_ALIGN_BOTTOMLEFT));

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>Chart::size(8),"R"=>0,"G"=>0,"B"=>0));

/* Define the chart area */
$myPicture->setGraphArea(Chart::size(60),Chart::size(30),Chart::size(915),Chart::size($h - 5));

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
    "Pos"=>SCALE_POS_TOPBOTTOM,
    "Mode" => SCALE_MODE_START0
);
$myPicture->drawScale($scaleSettings);


/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>Chart::size(1),"Y"=>Chart::size(1),"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the line chart */
//$myPicture->drawLineChart();
//$myPicture->drawPlotChart(array("PlotSize"=>1,"DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-50,"BorderAlpha"=>80));
$myPicture->drawBarChart();

/* Write the chart legend */
$myPicture->drawLegend(Chart::size(700),Chart::size(9),array(
    "Style"=>LEGEND_NOBORDER,
    "Mode"=>LEGEND_HORIZONTAL,
    "FontR"=>0,"FontG"=>0,"FontB"=>0,
    "FontName"=>PCHARTDIR."fonts/DejaVuSerifCondensed.ttf",
    "FontSize"=>Chart::size(9)
));

/* Render the picture */
$myPicture->stroke();
