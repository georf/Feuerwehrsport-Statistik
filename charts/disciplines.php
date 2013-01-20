<?php
$MyData = Cache::get();

if (!$MyData) {


    $scores = $db->getRows("
        SELECT `s`.`discipline_id`, `p`.`sex`
        FROM `scores` `s`
        INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
    ");

    $diss = array(
        'male1' => array(
            'name' => 'HL',
            'counter' => 0
        ),
        'male2' => array(
            'name' => 'HB m',
            'counter' => 0
        ),
        'female2' => array(
            'name' => 'HB w',
            'counter' => 0
        )
    );

    foreach ($scores as $s) {
        $diss[$s['sex'].$s['discipline_id']]['counter']++;
    }

    $points = array();
    $labels = array();
    $all = 0;
    foreach ($diss as $d) {
        $all += $d['counter'];
    }
    foreach ($diss as $d) {
        $points[] = round($d['counter']/$all,3)*100;
        $labels[] = $d['name'];
    }

    $MyData = new pData();
    $MyData->addPoints($points, "time");
    $MyData->addPoints($labels, "Platzierung");
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

    $w =120;
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
