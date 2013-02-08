<?php


$fullData = Cache::get();

if (!$fullData) {
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
                SELECT
                    `s`.`time`,`c`.`date`
                FROM `scores_gruppenstafette` `s`
                INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                WHERE
                `time` IS NOT NULL AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                )
                ORDER BY `c`.`date`
            ");
            $title = FSS::dis2name($key);
            break;

        case 'fs':
            $scores = $db->getRows("
                SELECT
                    `s`.`time`,`c`.`date`
                FROM `scores_stafette` `s`
                INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                WHERE
                `time` IS NOT NULL AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                )
                ORDER BY `c`.`date`
            ");
            $title = FSS::dis2name($key);
            break;

        case 'la':
            $scores = $db->getRows("
                SELECT
                    `s`.`time`,`c`.`date`
                FROM `scores_loeschangriff` `s`
                INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                WHERE
                `time` IS NOT NULL AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                    OR `person_7` = '".$id."'
                )
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

    $fullData = array(
        'title' => $title,
        'myData' => $MyData,
        'person' => $person,
    );
    Cache::put($fullData);
}

$MyData = $fullData['myData'];
$title = $fullData['title'];
$person = $fullData['person'];


/* Create the cache object */
$MyCache = new pCache();

/* Compute the hash linked to the chart data */
$ChartHash = $MyCache->getHash($MyData, Cache::getId());


/* Test if we got this hash in our cache already */
if ($MyCache->isInCache($ChartHash)) {

    /* If we have it, get the picture from the cache! */
    $MyCache->strokeFromCache($ChartHash);
} else {

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

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
