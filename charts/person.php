<?php

$_discipline = 1;
$_id = 0;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $_id = $_GET['id'];
} else {
  exit();
}

if (isset($_GET['discipline'])) {
  $_discipline = $_GET['discipline'];
}


$person = $db->getFirstRow("
    SELECT *
    FROM `persons`
    WHERE `id` = '".$db->escape($_id)."'
  ");

if (!$person) exit();

$MyData = Cache::get();

if (!$MyData) {


    if ($_discipline == 3) {

        $scores = $db->getRows("
          SELECT *
          FROM (
            SELECT *
            FROM (
                 SELECT
                    `hl`.`time` AS `hlTime`, `hb`.`time` AS `hbTime`,
                    (`hl`.`time` + `hb`.`time`) AS `time`,
                    `p`.`name` AS `place`,`e`.`name` AS `event`,`c`.`date`,`c`.`id`
                  FROM `scores` `hl`
                  INNER JOIN `scores` `hb` ON `hb`.`competition_id` = `hl`.`competition_id`
                  INNER JOIN `competitions` `c` ON `c`.`id` = `hl`.`competition_id`
                  INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
                  INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
                  WHERE `hl`.`person_id` = '".$db->escape($_id)."'
                  AND `hl`.`discipline_id` = 1
                  AND `hl`.`time` IS NOT NULL
                  AND `hb`.`person_id` = '".$db->escape($_id)."'
                  AND `hb`.`discipline_id` = 2
                  AND `hb`.`time` IS NOT NULL
                  ORDER BY `time`
            ) `i`
            GROUP BY `i`.`id`
          ) `i2`
          ORDER BY `i2`.`date`
        ");
    } else {

        $scores = $db->getRows("
          SELECT *
          FROM (
            SELECT *
            FROM (
              SELECT `s`.`time`,`p`.`name` AS `place`,`e`.`name` AS `event`,`c`.`date`,`c`.`id`
              FROM `scores` `s`
              INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
              INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
              INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
              WHERE `s`.`person_id` = '".$db->escape($_id)."'
              AND `s`.`discipline_id` = '".$db->escape($_discipline)."'
              AND `s`.`time` IS NOT NULL
              ORDER BY `s`.`time`
            ) `i`
            GROUP BY `i`.`id`
          ) `i2`
          ORDER BY `i2`.`date`
        ");
    }

    $times = array();
    $labels = array();

    foreach ($scores as $score) {
      $times[] = $score['time']/100;
      $labels[] = mb_substr($score['event'],0, 10, 'UTF-8').' '.date('Y',strtotime($score['date']))."\n".mb_substr($score['place'],0,15, 'UTF-8');
    }


    if ($_discipline == '1') {
        $legend = 'Hakenleitersteigen';
    } elseif ($_discipline == '2') {
        $legend = 'Hindernisbahn';
    } else {
        $legend = 'Zweikampf';
    }


    $MyData = new pData();
    $MyData->addPoints($times, $legend);
    $MyData->addPoints($labels, "Daten");
    $MyData->setAbscissa("Daten");


    Cache::put($MyData);
}

/* Create the cache object */
$MyCache = new pCache();

/* Compute the hash linked to the chart data */
$ChartHash = $MyCache->getHash($MyData);

/* Test if we got this hash in our cache already */
if ( $MyCache->isInCache($ChartHash)) {

    /* If we have it, get the picture from the cache! */
    $MyCache->strokeFromCache($ChartHash);
} else {


    $w = 700;
    $h = 300;
    $title = $person['firstname'].' '.$person['name'];

    /* Create the pChart object */
    $myPicture = new pImage($w, $h, $MyData);

    /* Turn of Antialiasing */
    $myPicture->Antialias = FALSE;

    /* Draw the background #9FC5EE */
    $Settings = array("R"=>169, "G"=>217, "B"=>238);
    $myPicture->drawFilledRectangle(0, 0, $w, $h, $Settings);

    $Settings = array(
      "StartR"=>159, "StartG"=>197, "StartB"=>238,
      "EndR"=>133, "EndG"=>184, "EndB"=>238,
      "Alpha"=>80
    );
    $myPicture->drawGradientArea(0, 0, $w, 20,DIRECTION_VERTICAL, $Settings);

    /* Add a border to the picture #87A8CC*/
    $myPicture->drawRectangle(0, 0, $w-1, $h-1,array("R"=>135, "G"=>168, "B"=>204));

    /* Write the chart title */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/calibri.ttf","FontSize"=>8,"R"=>255,"G"=>255,"B"=>255));
    $myPicture->drawText(10, 18, $title,array("FontSize"=>11,"Align"=>TEXT_ALIGN_BOTTOMLEFT));

    /* Set the default font */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>7,"R"=>0,"G"=>0,"B"=>0));

    /* Define the chart area */
    $myPicture->setGraphArea(25,25,682,215);

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
    $myPicture->drawPlotChart(array("PlotSize"=>1,"DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-50,"BorderAlpha"=>80));

    /* Write the chart legend */
    $myPicture->drawLegend(500,10,array(
      "Style"=>LEGEND_NOBORDER,
      "Mode"=>LEGEND_HORIZONTAL,
      "FontR"=>255,"FontG"=>255,"FontB"=>255,
      "FontName"=>PCHARTDIR."fonts/calibri.ttf",
      "FontSize"=>10
    ));


    /* Draw the standard mean and the geometric one */
    $Mean = $MyData->getSerieAverage($legend);
    $myPicture->drawThreshold($Mean,array("WriteCaption"=>TRUE,"Caption"=>"Durchscnnitt","CaptionAlign"=>CAPTION_RIGHT_BOTTOM));

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
