<?php

if (!Check::get('id') || !Check::isIn($_GET['id'], 'persons'))  throw new Exception('bad input');

$_id = $_GET['id'];
$id = $_id;


$storage = Cache::get();

if (!$storage) {

    $positions = array();
    $labels = array();
    for ($i = 1; $i < 8; $i++) {
        $positions[] = $db->getFirstRow("
            SELECT COUNT(`id`) AS `count`
            FROM `scores_loeschangriff`
            WHERE `person_".$i."` = ".$id."
        ", 'count');
    }

    $show = array();
    for ($i = 1; $i < 8; $i++) {
        if ($positions[$i - 1] != 0) {
            $show[] = $positions[$i - 1];
            $labels[] = getLWK($i);
        }
    }

    $MyData = new pData();
    $MyData->addPoints($show, "time");
    $MyData->addPoints($labels, "labels");
    $MyData->setAbscissa("labels");

    $storage = array(
        'data' => $MyData,
        'positions' => $positions
    );

    Cache::put($storage);
}

$MyData = $storage['data'];
$positions = $storage['positions'];

/* Create the cache object */
$MyCache = new pCache();

/* Compute the hash linked to the chart data */
$ChartHash = $MyCache->getHash($MyData, Cache::getId());

/* Test if we got this hash in our cache already */
if ($MyCache->isInCache($ChartHash)) {

    /* If we have it, get the picture from the cache! */
    $MyCache->strokeFromCache($ChartHash);
} else {

    $all = 0;
    foreach ($positions as $p) {
        $all += $p;
    }

    $w = 920;
    $h = 155;

    /* Create the pChart object */
    $myPicture = new pImage($w, $h, $MyData, TRUE);

    /* Turn on Antialiasing */
    $myPicture->Antialias = TRUE;

    /* Set the default font */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>9,"R"=>0,"G"=>0,"B"=>0));

    $myPicture->setShadow(FALSE);
    $myPicture->drawFromPNG(0,0, __DIR__."/images/la.png");


    if ($all != 0) {
        $wks = array(
            array(77,68),
            array(77,45),
            array(77,25),
            array(295,82),
            array(630,35),
            array(459,77),
            array(630,119),
        );

        foreach ($wks as $i => $wk) {
            if ($positions[$i] == 0) continue;

            $pro = $positions[$i]/$all;

            $myPicture->drawFilledCircle($wk[0],$wk[1],30*$pro+8, array("R"=>104,"G"=>245, "B"=>63,"Alpha"=>30*$pro+60));
        }
    }


    /* Create the pPie object */
    $PieChart = new pPie($myPicture, $MyData);
    /* Draw a simple pie chart */
    $PieChart->draw2DPie(Chart::size(750),Chart::size(72), array(
        "WriteValues"=>PIE_VALUE_PERCENTAGE,
        "ValueR"=>50,
        "ValueG"=>50,
        "ValueB"=>50,
        "ValueAlpha"=>100,
        "Border"=>TRUE,
        "ValuePosition"=>PIE_VALUE_INSIDE,
        "SkewFactor"=>0.5,
        "Radius"=>Chart::size(38),
        "ValuePadding"=>Chart::size(15),
        "LabelStacked"=>true
    ));

    $PieChart->drawPieLegend(Chart::size(795),Chart::size(17));

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
