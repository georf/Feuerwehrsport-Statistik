<?php


$fullData = Cache::get();

if (!$fullData) {
    if (!Check::get('key')) throw new Exception('not enough arguments');
    if (!in_array($_GET['key'], array('hl', 'hb'))) throw new Exception('bad key');

    $scores = array();
    $title  = '';

    $MyData = new pData();


    $sex = array('female', 'male');
    foreach ($sex as $s) {

        $scores = $db->getRows("
            SELECT MIN( `time` ) AS `time`
            FROM `scores` `s`
            INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
            WHERE `s`.`time` IS NOT NULL
            AND `discipline` LIKE '".$db->escape($_GET['key'])."'
            AND `p`.`sex` = '".$s."'
            GROUP BY `person_id`
            ORDER BY `time`
        ");

        $points = array();
        $labels = array();
        $i = 1;
        foreach ($scores as $score) {
            $points[] = intval($score['time'])/100;
            $labels[] = $i.'.';
            $i++;
        }

        $MyData->addPoints($points, "time".$s);
        $MyData->addPoints($labels, "labels".$s);
        $MyData->setSerieDescription("time".$s, 'Bestzeiten '.FSS::sex($s));
        $MyData->setPalette('time'.$s, FSS::palette($s));

    }
    
    $fullData = array(
        'title' => $title,
        'myData' => $MyData,
        'sex' => $sex
    );
    Cache::put($fullData);
}

$MyData = $fullData['myData'];
$title = $fullData['title'];
$sex = $fullData['sex'];


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
    $h = 230;
    
    if (count($sex) == 2) $h *=2;
    
    $myPicture = Chart::create($w, $h, $MyData);

    /* Turn of Antialiasing */
    $myPicture->Antialias = FALSE;

    /* Set the default font */
    $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf", "FontSize"=>Chart::size(7), "R"=>0, "G"=>0, "B"=>0));

    $allSeries = array('timefemale', 'timemale', 'labelsfemale', 'labelsmale');
    $MyData->setSerieDrawable($allSeries, false);
    $MyData->setSerieDrawable(array('timefemale', 'timemale'), true);
    
    /* Write the chart legend */
    $myPicture->drawLegend(Chart::size(50),Chart::size(10),array(
      "Style"=>LEGEND_NOBORDER,
      "Mode"=>LEGEND_HORIZONTAL,
      "FontR"=>0,"FontG"=>0,"FontB"=>0,
      "FontName"=>PCHARTDIR."fonts/calibri.ttf",
      "FontSize"=>Chart::size(10)
    ));

    for ($i = 0; $i < count($sex); $i++) {
        $s = $sex[$i];
        
        $MyData->setSerieDrawable($allSeries, false);
        
        $MyData->setSerieDrawable('time'.$s, true);
        $MyData->setSerieDrawable('labels'.$s, true);
        $MyData->setAbscissa("labels".$s);
        
        /* Define the chart area */
        $myPicture->setGraphArea(Chart::size(20), Chart::size(20 + 230 * $i), Chart::size(690), Chart::size(210 + 230 * $i));

        /* Draw the scale */
        $scaleSettings = array(
          "XMargin"=>Chart::size(10),
          "YMargin"=>Chart::size(10),
          "Floating"=>TRUE,
          "GridR"=>200,
          "GridG"=>200,
          "GridB"=>200,
          "DrawSubTicks"=>false,
          "CycleBackground"=>false,
          "LabelSkip"=>49
        );
        $myPicture->drawScale($scaleSettings);

        /* Turn on Antialiasing */
        $myPicture->Antialias = TRUE;

        /* Enable shadow computing */
        $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

        /* Draw the line chart */
        $myPicture->drawLineChart();

        /* Draw the standard mean and the geometric one */
        $Mean = $MyData->getSerieAverage("time".$s);
        $myPicture->drawThreshold($Mean,array("WriteCaption"=>TRUE,"Caption"=>"Durchscnnitt","CaptionAlign"=>CAPTION_RIGHT_BOTTOM));

    }
    
    
    /* Render the picture */
    $myPicture->stroke();

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
