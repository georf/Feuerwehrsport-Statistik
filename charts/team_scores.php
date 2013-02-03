<?php


$fullData = Cache::get();

if (true || !$fullData) {
    if (!Check::get('id', 'key')) throw new Exception('not enough arguments');
    if (!Check::isIn($_GET['id'], 'competitions')) throw new Exception('bad competition');
    $id = intval($_GET['id']);

    $keys = explode('-', $_GET['key']);
    $key = $keys[0];

    $sex = false;
    $final = false;
    if (count($keys) > 1) {
        if (!empty($keys[1])) $sex = $keys[1];
        if (count($keys) > 2) {
            $final = true;
        }
    }

    if ($sex && !in_array($sex, array('male', 'female'))) throw new Exception('bad sex');

    $scores = array();
    $title  = '';

    switch ($key) {
        case 'gs':

            $scores = $db->getRows("
                SELECT `g`.`time`,`c`.`date` AS `date`
                FROM (
                    SELECT *
                    FROM
                    (
                        SELECT `competition_id`,`time`
                        FROM `scores_gruppenstafette`
                        WHERE `time` IS NOT NULL
                        AND `team_id` = '".$id."'
                        ORDER BY `time`
                    ) `i`
                    GROUP BY `competition_id`
                ) `g`

                INNER JOIN `competitions` `c` ON `c`.`id` = `g`.`competition_id`
                ORDER BY `c`.`date`
            ");
            $title = FSS::dis2name($key);
            break;

        case 'la':
            if (!$sex) throw new Exception('sex not defined');

            $scores = $db->getRows("
                SELECT `g`.`time`,`c`.`date` AS `date`
                FROM (
                    SELECT *
                    FROM
                    (
                        SELECT `competition_id`,`time`
                        FROM `scores_loeschangriff`
                        WHERE `time` IS NOT NULL
                        AND `team_id` = '".$id."'
                        AND `sex` = '".$sex."'
                        ORDER BY `time`
                    ) `i`
                    GROUP BY `competition_id`
                ) `g`

                INNER JOIN `competitions` `c` ON `c`.`id` = `g`.`competition_id`
                ORDER BY `c`.`date`
            ");
            $title = FSS::dis2name($key).' '.FSS::sex($sex);
            break;

        case 'fs':
            if (!$sex) throw new Exception('sex not defined');

            $scores = $db->getRows("
                SELECT `g`.`time`,`c`.`date` AS `date`
                FROM (
                    SELECT *
                    FROM
                    (
                        SELECT `competition_id`,`time`
                        FROM `scores_stafette`
                        WHERE `time` IS NOT NULL
                        AND `team_id` = '".$id."'
                        AND `sex` = '".$sex."'
                        ORDER BY `time`
                    ) `i`
                    GROUP BY `competition_id`
                ) `g`

                INNER JOIN `competitions` `c` ON `c`.`id` = `g`.`competition_id`
                ORDER BY `c`.`date`
            ");
            $title = FSS::dis2name($key).' '.FSS::sex($sex);
            break;

        default:
            throw new Exception('bad key');
            break;
    }

    $points = array();
    $labels = array();
    $i = 1;
    foreach ($scores as $score) {
      $points[] = intval($score['time'])/100;
      $labels[] = gDate($score['date']);
      $i++;
    }

    $MyData = new pData();
    $MyData->addPoints($points, "time");

    $MyData->addPoints($labels, "Daten");
    $MyData->setAbscissa("Daten");
    $MyData->setSerieDescription("time", 'Zeit');


    $fullData = array(
        'title' => $title,
        'myData' => $MyData
    );
    Cache::put($fullData);
}

$MyData = $fullData['myData'];
$title = $fullData['title'];


/* Create the cache object */
$MyCache = new pCache();

/* Compute the hash linked to the chart data */
$ChartHash = $MyCache->getHash($MyData, Cache::getId());


/* Test if we got this hash in our cache already */
if (false && $MyCache->isInCache($ChartHash)) {

    /* If we have it, get the picture from the cache! */
    $MyCache->strokeFromCache($ChartHash);
} else {

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


    /* Draw the standard mean and the geometric one */
    $Mean = $MyData->getSerieAverage("time");
    $myPicture->drawThreshold($Mean,array("WriteCaption"=>TRUE,"Caption"=>"Durchscnnitt","CaptionAlign"=>CAPTION_RIGHT_BOTTOM));


    /* Push the rendered picture to the cache */
    //$MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
