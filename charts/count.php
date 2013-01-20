<?php
$MyData = Cache::get();

if (!$MyData) {

    $competitions = $db->getRows("
      SELECT `c`.*,`p`.`name` AS `place`
      FROM `competitions` `c`
      INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
      ORDER BY `c`.`date`;
    ");

    $diss = array(array(
            'name' => 'HL',
            'dis' => 1,
            'sex' => 'male',
            'counter' => array()
        ), array(
            'name' => 'HB männlich',
            'dis' => 2,
            'sex' => 'male',
            'counter' => array()
        ), array(
            'name' => 'HB weiblich',
            'dis' => 2,
            'sex' => 'female',
            'counter' => array()
        )
    );

    $labels = array();

    foreach ($competitions as $competition) {
        $labels[] = mb_substr($competition['place'], 0, 6,'UTF-8').' '.date('y',strtotime($competition['date']));

        foreach ($diss as $key => $dis) {
            $scores = $db->getRows("
              SELECT `time`
              FROM (
                SELECT `s`.*
                FROM `scores` `s`
                INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                WHERE `s`.`competition_id` = '".$competition['id']."'
                AND `s`.`discipline_id` = '".$db->escape($dis['dis'])."'
                AND `p`.`sex` = '".$db->escape($dis['sex'])."'
                ORDER BY `s`.`time`) `i`
              GROUP BY `i`.`person_id`
            ");

            if (!$scores || count($scores) <= 0) {
                $dis['counter'][] = VOID;
            } else {
                $dis['counter'][] = count($scores);
            }
            $diss[$key] = $dis;
        }
    }


    $MyData = new pData();
    $MyData->addPoints($labels, 'Labels');
    $MyData->setAbscissa('Labels');

    foreach ($diss as $key => $dis) {
        $MyData->addPoints($dis['counter'], $dis['name']);
    }

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



    $w = 920;
    $h = 265;
    $title = 'Anzahl der Zeiten pro Wettkampf';
    /* Create the pChart object */
    $myPicture = new pImage($w, $h, $MyData, FALSE);

    /* Turn on Antialiasing */
    $myPicture->Antialias = TRUE;

    /* Write the chart title */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/DejaVuSerifCondensed.ttf","FontSize"=>8,"R"=>0,"G"=>0,"B"=>0));
    $myPicture->drawText(10, 18, 'Anzahl der Teilnehmer pro Wettkampf',array("FontSize"=>11,"Align"=>TEXT_ALIGN_BOTTOMLEFT));

    /* Set the default font */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>8,"R"=>0,"G"=>0,"B"=>0));

    /* Define the chart area */
    $myPicture->setGraphArea(20,18,915,200);

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
      "LabelRotation"=>90,
      "Mode" => SCALE_MODE_START0
    );
    $myPicture->drawScale($scaleSettings);


    /* Enable shadow computing */
    $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

    /* Draw the line chart */
    //$myPicture->drawLineChart();
    //$myPicture->drawPlotChart(array("PlotSize"=>1,"DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-50,"BorderAlpha"=>80));
    $myPicture->drawBarChart();

    /* Write the chart legend */
    $myPicture->drawLegend(700,9,array(
      "Style"=>LEGEND_NOBORDER,
      "Mode"=>LEGEND_HORIZONTAL,
      "FontR"=>0,"FontG"=>0,"FontB"=>0,
      "FontName"=>PCHARTDIR."fonts/DejaVuSerifCondensed.ttf",
      "FontSize"=>9
    ));

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
