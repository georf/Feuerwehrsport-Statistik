<?php

$_discipline = 1;
$_sex = 'male';
$_id = 0;

$debug = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $_id = $_GET['id'];
} else {
  exit();
}

if (isset($_GET['discipline']) && is_numeric($_GET['discipline'])) {
  $_discipline = $_GET['discipline'];
}

if (isset($_GET['sex'])) {
  $_sex = $_GET['sex'];
}



$MyData = Cache::get();

if (!$MyData) {


    $event = $db->getFirstRow("
        SELECT *
        FROM `events`
        WHERE `id` = '".$db->escape($_id)."'
        LIMIT 1;");

    if (!$event) {
        exit();
    }
    $id = $event['id'];


    $competitions = $db->getRows("
      SELECT `c`.*,`p`.`name` AS `place`
      FROM `competitions` `c`
      INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
      WHERE `c`.`event_id` = '".$id."'
      ORDER BY `c`.`date`;
    ");


    $open   = array();
    $close  = array();
    $max    = array();
    $min    = array();
    $median = array();
    $label  = array();
    $ave    = array();
    $ave5   = array();


    foreach ($competitions as $competition) {
        $scores = $db->getRows("
            SELECT `time`
            FROM (
              SELECT `time`
              FROM (
                SELECT `s`.*
                FROM `scores` `s`
                INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                WHERE `s`.`competition_id` = '".$competition['id']."'
                AND `s`.`discipline_id` = '".$db->escape($_discipline)."'
                AND `p`.`sex` = '".$db->escape($_sex)."'
                AND `s`.`time` IS NOT NULL
                ORDER BY `s`.`time`) `i`
              GROUP BY `i`.`person_id`
            ) `i2`
            ORDER BY `i2`.`time`
        ");


        if (!$scores || count($scores) < 0) {
            continue;
        }
        $n = count($scores);


        $sum = 0;
        $sum5 = 0;
        $i = 0;
        foreach ($scores as $score) {
            $i++;
            $sum += $score['time'];
            if ($i == 5) {
                $sum5 = $sum;
            }
        }

        $ave[] = c2s(round($sum/$n,2));

        if ($sum5 == 0) {
            $ave5[] = $sum;
        } else {
            $ave5[] = c2s(round($sum5/5,2));
        }


        $min[] = c2s($scores[0]['time']);
        $max[] = c2s($scores[$n-1]['time']);
        $open[] = c2s($scores[round(0.25 * ($n+1))-1]['time']);
        $close[] = c2s($scores[round(0.75 * ($n+1))-1]['time']);

        if ($n%2 == 0) {
            $median[] = c2s(($scores[$n/2]['time']+$scores[$n/2+1]['time'])/2);
        } else {
            $median[] = c2s($scores[($n+1)/2]['time']);
        }

        $label[] = substr($competition['place'], 0, 5).' '.date('y',strtotime($competition['date']));
    }

    /* Create and populate the pData object */
    $MyData = new pData();
    $MyData->addPoints($open, "Open");
    $MyData->addPoints($close, "Close");
    $MyData->addPoints($min, "Min");
    $MyData->addPoints($max, "Max");
    $MyData->addPoints($median, "Median");
    $MyData->addPoints($ave, "Durchschnitt");
    $MyData->addPoints($ave5, "Beste 5");

    $MyData->addPoints($label, "Events");
    $MyData->setAbscissa("Events");



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
    $h = 400;
    $title = 'Zeiten in Quartile - Ort';
    $legend = 'Hakenleitersteigen';
    if ($_discipline != 1) {
      if ($_sex == 'male') {
        $legend = 'Hindernisbahn - Männer';
      } else {
        $legend = 'Hindernisbahn - Frauen';
      }
    }
    $title .= '          '.$legend;

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
    $myPicture->setGraphArea(40,30,660,340);

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

    /* Create the pStock object */
    $mystockChart = new pStock($myPicture,$MyData);

    /* Draw the stock chart */
    $stockSettings = array(
        "BoxUpR"=>255,
        "BoxUpG"=>255,
        "BoxUpB"=>255,
        "BoxDownR"=>255,
        "BoxDownG"=>255,
        "BoxDownB"=>255,
        "SerieMedian"=>"Median");
    $mystockChart->drawStockChart($stockSettings);

    $MyData->setSerieDrawable("Open", FALSE);
    $MyData->setSerieDrawable("Close", FALSE);
    $MyData->setSerieDrawable("Max", FALSE);
    $MyData->setSerieDrawable("Min", FALSE);
    $MyData->setSerieDrawable("Median", FALSE);
    $MyData->setSerieDrawable("Durchschnitt", TRUE);
    $MyData->setSerieDrawable("Beste 5", TRUE);

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

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
