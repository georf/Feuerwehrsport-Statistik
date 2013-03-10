<?php

$_discipline = 1;
$_sex = 'male';

$debug = false;

if (isset($_GET['discipline']) && is_numeric($_GET['discipline'])) {
  $_discipline = $_GET['discipline'];
}

if (isset($_GET['sex'])) {
  $_sex = $_GET['sex'];
}

$MyData = Cache::get();

if (!$MyData) {

    $events = $db->getRows("
        SELECT `e`.*, COUNT(`c`.`id`) AS `count`
        FROM `events` `e`
        INNER JOIN `competitions` `c` ON `c`.`event_id` = `e`.`id`
        GROUP BY `e`.`id`
    ");


    $open   = array();
    $close  = array();
    $max    = array();
    $min    = array();
    $median = array();
    $label  = array();


    foreach ($events as $event) {
        $competitions = $db->getRows("
          SELECT `c`.*,`p`.`name` AS `place`
          FROM `competitions` `c`
          INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
          WHERE `c`.`event_id` = '".$event['id']."'
          ORDER BY `c`.`date`;
        ");

        $times = array();

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

            foreach ($scores as $score) {
                $times[] = $score['time'];
            }
        }

        if (!$times || count($times) < 0) {
            continue;
        }
        $n = count($times);
        if ($n == 0) continue;


        $sum = 0;
        $sum5 = 0;
        $i = 0;
        foreach ($times as $time) {
            $i++;
            $sum += $time;
            if ($i == 5) {
                $sum5 = $sum;
            }
        }

        sort($times);

        $min[] = c2s($times[0]);
        $max[] = c2s($times[$n-1]);
        $open[] = c2s($times[round(0.25 * ($n+1))-1]);
        $close[] = c2s($times[round(0.75 * ($n+1))-1]);

        if ($n%2 == 0) {
            $median[] = c2s(($times[($n/2)-1]+$times[($n/2+1)-1])/2);
        } else {
            $median[] = c2s($times[(($n+1)/2)-1]);
        }

        $label[] = mb_substr($event['name'], 0, 10, 'UTF-8');
    }


    /* Create and populate the pData object */
    $MyData = new pData();
    $MyData->addPoints($open, "Open");
    $MyData->addPoints($close, "Close");
    $MyData->addPoints($min, "Min");
    $MyData->addPoints($max, "Max");
    $MyData->addPoints($median, "Median");

    $MyData->addPoints($label, "Events");
    $MyData->setAbscissa("Events");


    Cache::put($MyData);
}

/* Create the cache object */
$MyCache = new pCache();

/* Compute the hash linked to the chart data */
$ChartHash = $MyCache->getHash($MyData, Cache::getId());

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


    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
