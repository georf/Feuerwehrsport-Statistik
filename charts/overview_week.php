<?php
$MyData = Cache::get();

if (!$MyData) {

    $months = array();
    $labels = array();

    for ($i = 1; $i < 8; $i++) {
        $c = $i%7+1;
        $months[$i] = $db->getFirstRow("
          SELECT COUNT(*) AS `count`
          FROM `competitions`
          WHERE DAYOFWEEK(`date`) = '".$c."'
        ", 'count');
        $labels[$i] = strftime('%a', mktime(1,1,1,4,$c,2012));
    }


    $MyData = new pData();
    $MyData->addPoints($labels, "Labels");
    $MyData->addPoints($months, 'Anzahl der Wettkämpfe');
    $MyData->setSerieDescription("Labels", "Months");
    $MyData->setAbscissa("Labels");


    $MyData = array(
        'max' => max($months),
        'data' => $MyData
    );
    Cache::put($MyData);
}

$max = $MyData['max'];
$MyData = $MyData['data'];

/* Create the cache object */
$MyCache = new pCache();

/* Compute the hash linked to the chart data */
$ChartHash = $MyCache->getHash($MyData, Cache::getId());

/* Test if we got this hash in our cache already */
if ($MyCache->isInCache($ChartHash)) {

    /* If we have it, get the picture from the cache! */
    $MyCache->strokeFromCache($ChartHash);
} else {



    $w = 170;
    $h = 150;

    /* Create the pChart object */
    $myPicture = Chart::create($w, $h, $MyData);

    /* Turn on Antialiasing */
    $myPicture->Antialias = TRUE;

    /* Set the default font */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>Chart::size(8),"R"=>0,"G"=>0,"B"=>0));

    /* Define the chart area */
    $myPicture->setGraphArea(Chart::size(19),Chart::size(18),Chart::size(150),Chart::size(125));

    /* Draw the scale */
    $scaleSettings = array(
      "XMargin"=>0,
      "YMargin"=>0,
      "GridR"=>220,
      "GridG"=>220,
      "GridB"=>220,
      "LabelRotation"=>90,
      "Mode" => SCALE_MODE_MANUAL,
      "ManualScale" => array(array('Min'=>0, 'Max'=>$max+2)),
      "CycleBackground"=>TRUE
    );
    $myPicture->drawScale($scaleSettings);

    /* Enable shadow computing */
    $myPicture->setShadow(TRUE,array("X"=>Chart::size(1),"Y"=>Chart::size(1),"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

    /* Draw the line chart */
    $myPicture->drawBarChart(array("DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE));

    /* Write the chart legend */
    $myPicture->drawLegend(Chart::size(5),Chart::size(4),array(
      "Style"=>LEGEND_NOBORDER,
      "Mode"=>LEGEND_HORIZONTAL,
      "FontR"=>0,"FontG"=>0,"FontB"=>0,
      "FontName"=>PCHARTDIR."fonts/calibri.ttf",
      "FontSize"=>Chart::size(10)
    ));

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
