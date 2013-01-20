<?php

$_sex = 'male';
$_id = 0;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $_id = $_GET['id'];
} else {
  exit();
}

if (isset($_GET['sex'])) {
  $_sex = $_GET['sex'];
}


$MyData = Cache::get();

if (!$MyData) {


    $competition = $db->getFirstRow("
      SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`
      FROM `competitions` `c`
      INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
      INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
      WHERE `c`.`id` = '".$db->escape($_GET['id'])."'
      LIMIT 1;");

    $scores = $db->getRows("
      SELECT `time`
      FROM (
        SELECT `s`.*
        FROM `scores_loeschangriff` `s`
        WHERE `s`.`competition_id` = '".$db->escape($_id)."'
        AND `s`.`sex` = '".$db->escape($_sex)."'
        AND `s`.`time` IS NOT NULL
        ORDER BY `s`.`time`) `i`
      GROUP BY `i`.`team_id`;
    ");

    $points = array();
    $labels = array();
    $i = 1;
    foreach ($scores as $score) {
      $points[] = intval($score['time'])/100;
      $labels[] = $i.'.';
      $i++;
    }

    sort($points);

    $MyData = new pData();
    $MyData->addPoints($points, "time");
    $MyData->addPoints($labels, "Platzierung");
    $MyData->setAbscissa("Platzierung");


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
    $h = 230;
    $title = 'Zeit - Platzierung';
    if ($_sex == 'male') {
        $legend = 'Löschangriff - Männer';
    } else {
        $legend = 'Löschangriff - Frauen';
    }
    $MyData->setSerieDescription("time", $legend);

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
    $myPicture->setGraphArea(40,30,660,200);

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
      "LabelSkip"=>2
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
    $Mean = $MyData->getSerieAverage("time");
    $myPicture->drawThreshold($Mean,array("WriteCaption"=>TRUE,"Caption"=>"Durchscnnitt","CaptionAlign"=>CAPTION_RIGHT_BOTTOM));


    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
