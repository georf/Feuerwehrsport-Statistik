<?php
$MyData = Cache::get();

if (!$MyData) {

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
                    FROM `scores_loeschangriff`
                    WHERE `competition_id` = '".$competition['id']."'
                UNION
                    SELECT CONCAT(CAST(`team_id` AS CHAR),`sex`,CAST(`team_number` AS CHAR)) AS `team`
                    FROM `scores_stafette`
                    WHERE `competition_id` = '".$competition['id']."'
                UNION
                    SELECT CONCAT(CAST(`team_id` AS CHAR),'female',CAST(`team_number` AS CHAR)) AS `team`
                    FROM `scores_gruppenstafette`
                    WHERE `competition_id` = '".$competition['id']."'
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


    Cache::put($MyData);
}

/* Create the cache object */
$MyCache = new pCache();

/* Compute the hash linked to the chart data */
$ChartHash = $MyCache->getHash($MyData, Cache::getId());

/* Test if we got this hash in our cache already */
if ($MyCache->isInCache($ChartHash)) {

    /* If we have it, get the picture from the cache! */
    $MyCache->strokeFromCache($ChartHash);
} else {



    $w = 920;
    $h = 265;
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
    $myPicture->setGraphArea(Chart::size(20),Chart::size(18),Chart::size(915),Chart::size(200));

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

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
