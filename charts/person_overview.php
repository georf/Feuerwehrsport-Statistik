<?php

$_id = 0;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $_id = $_GET['id'];
} else {
  exit();
}


$MyData = Cache::get();

if (!$MyData) {

    $person = $db->getFirstRow("
        SELECT *
        FROM `persons`
        WHERE `id` = '".$db->escape($_id)."'
      ");

    if (!$person) exit();


    $years = $db->getRows("
        SELECT YEAR(`date`) AS `year`
        FROM `competitions`
        GROUP BY YEAR(`date`)
        ORDER BY `year`
    ");

    if ($person['sex'] == 'male') {
        $diss = array(array(
                'name' => 'HL',
                'dis' => 1,
                'avgs' => array()
            ), array(
                'name' => 'HB',
                'dis' => 2,
                'avgs' => array()
            )
        );
    } else {
        $diss = array(array(
                'name' => 'HB',
                'dis' => 2,
                'avgs' => array()
            )
        );
    }


    $labels  = array();


    foreach ($years as $year) {
        $labels[] = substr($year['year'],2);

        foreach ($diss as $key => $dis) {

            $avg = $db->getFirstRow("
                SELECT AVG(`i2`.`time`) AS `avg`
                FROM (
                    SELECT *
                    FROM (
                      SELECT `s`.`time`,`p`.`name` AS `place`,`e`.`name` AS `event`,`c`.`date`,`c`.`id`
                      FROM `scores` `s`
                      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                      INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
                      INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
                      WHERE `s`.`person_id` = '".$db->escape($_id)."'
                      AND YEAR(`c`.`date`) = '".$year['year']."'
                      AND `s`.`discipline` = '".$db->escape($dis['name'])."'
                      AND `s`.`time` IS NOT NULL
                      ORDER BY `s`.`time`
                    ) `i`
                    GROUP BY `i`.`id`
                  ) `i2`
            ", 'avg');


            if (is_numeric($avg)) {
                $diss[$key]['avgs'][] = c2s($avg);
            } else {
                $diss[$key]['avgs'][] = VOID;
            }

        }
    }



    $MyData = new pData();
    $MyData->addPoints($labels, "Labels");
    foreach ($diss as $key => $dis) {
        $MyData->addPoints($dis['avgs'], $dis['name']);
    }
    $MyData->setSerieDescription("Labels", "Months");
    $MyData->setAbscissa("Labels");
    $MyData->setAxisName(0,'Zeiten');

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






    $w = 210;
    $h = 150;

    /* Create the pChart object */
    $myPicture = new pImage($w, $h, $MyData, TRUE);

    /* Turn on Antialiasing */
    $myPicture->Antialias = TRUE;

    /* Set the default font */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>8,"R"=>0,"G"=>0,"B"=>0));

    /* Define the chart area */
    $myPicture->setGraphArea(25,15,200,135);

    /* Draw the scale */
    $scaleSettings = array(
      "XMargin"=>0,
      "YMargin"=>0,
      "GridR"=>220,
      "GridG"=>220,
      "GridB"=>220,
      //"Mode" => SCALE_MODE_MANUAL,
      //"ManualScale" => array(array('Min'=>15, 'Max'=>21)),
      "CycleBackground"=>TRUE
    );
    $myPicture->drawScale($scaleSettings);

    /* Enable shadow computing */
    $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

    /* Draw the line chart */
    $myPicture->drawLineChart(array(
        'BreakVoid' => false,
    ));
    $myPicture->drawPlotChart(array(
        "PlotSize"=>1,
        "DisplayValues"=>FALSE,
        "PlotBorder"=>False,
        "BorderSize"=>1,
        "Surrounding"=>-50,
        "BorderAlpha"=>80
    ));


    /* Write the chart legend */
    $myPicture->drawLegend(5,1,array(
      "Style"=>LEGEND_NOBORDER,
      "Mode"=>LEGEND_HORIZONTAL,
      "FontR"=>0,"FontG"=>0,"FontB"=>0,
      "FontName"=>PCHARTDIR."fonts/calibri.ttf",
      "FontSize"=>10
    ));

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
