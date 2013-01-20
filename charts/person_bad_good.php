<?php

$_discipline = 1;
$_id = 0;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $_id = $_GET['id'];
} else {
  exit();
}

if (isset($_GET['discipline'])) {
  $_discipline = $_GET['discipline'];
}


$MyData = Cache::get();

if (!$MyData) {
    $person = $db->getFirstRow("
        SELECT *
        FROM `persons`
        WHERE `id` = '".$db->escape($_id)."'
      ");

    if (!$person) exit();


    $good = $db->getFirstRow("
          SELECT COUNT(*) AS `good`
          FROM `scores`
          WHERE `person_id` = '".$db->escape($_id)."'
          AND `discipline_id` = '".$db->escape($_discipline)."'
          AND `time` IS NOT NULL
    ", 'good');
    $bad = $db->getFirstRow("
          SELECT COUNT(*) AS `bad`
          FROM `scores`
          WHERE `person_id` = '".$db->escape($_id)."'
          AND `discipline_id` = '".$db->escape($_discipline)."'
          AND `time` IS NULL
    ", 'bad');


    $MyData = new pData();
    $MyData->addPoints(array($good, $bad), "time");
    $MyData->addPoints(array('Gültig', 'Ungültig'), "Platzierung");
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


    $w = 140;
    $h = 65;
    $title = '';



    /* Create the pChart object */
    $myPicture = new pImage($w, $h, $MyData, TRUE);

    /* Turn on Antialiasing */
    $myPicture->Antialias = TRUE;

    /* Set the default font */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>9,"R"=>0,"G"=>0,"B"=>0));

    /* Create the pPie object */
    $PieChart = new pPie($myPicture,$MyData);

    /* Draw a simple pie chart */
    $PieChart->draw2DPie(30,30,array(
        "WriteValues"=>PIE_VALUE_PERCENTAGE,
        "ValueR"=>50,
        "ValueG"=>50,
        "ValueB"=>50,
        "ValueAlpha"=>100,
        "Border"=>TRUE,
        "ValuePosition"=>PIE_VALUE_INSIDE,
        "SkewFactor"=>0.5,
        "Radius"=>30,
        "ValuePadding"=>"15"));

    $PieChart->drawPieLegend(68,17);

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
