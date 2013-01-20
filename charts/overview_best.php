<?php
$MyData = Cache::get();

if (!$MyData) {
    $years = $db->getRows("
        SELECT YEAR(`date`) AS `year`
        FROM `competitions`
        GROUP BY YEAR(`date`)
        ORDER BY `year`
    ");


    $diss = array(array(
            'name' => 'HL',
            'dis' => 1,
            'sex' => 'male',
            'avgs' => array()
        ), array(
            'name' => 'HB männlich',
            'dis' => 2,
            'sex' => 'male',
            'avgs' => array()
        ), array(
            'name' => 'HB weiblich',
            'dis' => 2,
            'sex' => 'female',
            'avgs' => array()
        )
    );

    $labels  = array();


    foreach ($years as $year) {
        $labels[] = substr($year['year'],2);

        foreach ($diss as $key => $dis) {
            $avgs = array();

            $competitions = $db->getRows("
              SELECT `c`.*,`p`.`name` AS `place`
              FROM `competitions` `c`
              INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
              WHERE YEAR(`c`.`date`) = '".$year['year']."'
              ORDER BY `c`.`date`;
            ");

            foreach ($competitions as $competition) {
                $count = $db->getRows("
                    SELECT `person_id`
                    FROM (
                        SELECT `s`.*, 1 AS `c`
                        FROM `scores` `s`
                        INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                        WHERE `s`.`competition_id` = '".$competition['id']."'
                        AND `s`.`discipline_id` = '".$db->escape($dis['dis'])."'
                        AND `p`.`sex` = '".$db->escape($dis['sex'])."'
                        AND `s`.`time` IS NOT NULL
                        ORDER BY `s`.`time`) `i`
                    GROUP BY `i`.`person_id`
                ");
                if (count($count) < 20) {
                    continue;
                }


                $avg = $db->getFirstRow("
                    SELECT AVG(`time`) AS `avg`
                    FROM (
                        SELECT `time`
                        FROM (
                          SELECT `time`
                          FROM (
                            SELECT `s`.*
                            FROM `scores` `s`
                            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                            WHERE `s`.`competition_id` = '".$competition['id']."'
                            AND `s`.`discipline_id` = '".$db->escape($dis['dis'])."'
                            AND `p`.`sex` = '".$db->escape($dis['sex'])."'
                            AND `s`.`time` IS NOT NULL
                            ORDER BY `s`.`time`) `i`
                          GROUP BY `i`.`person_id`
                        ) `i2`
                        ORDER BY `i2`.`time`
                        LIMIT 5
                    ) `i3`
                ", 'avg');

                if (is_numeric($avg)) $avgs[] = $avg;
            }

            $sum = 0;
            foreach ($avgs as $avg) $sum += $avg;
            $diss[$key]['avgs'][] = c2s($sum/count($avgs));

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
$ChartHash = $MyCache->getHash($MyData);

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
      "Mode" => SCALE_MODE_MANUAL,
      "ManualScale" => array(array('Min'=>15, 'Max'=>21)),
      "CycleBackground"=>TRUE
    );
    $myPicture->drawScale($scaleSettings);

    /* Enable shadow computing */
    $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

    /* Draw the line chart */
    $myPicture->drawLineChart();

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
