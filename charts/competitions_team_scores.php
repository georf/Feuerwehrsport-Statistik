<?php

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');

$fullData = Cache::get();

if (!$fullData) {

    if (Check::get('event') && Check::isIn($_GET['event'], 'events')) {
        $types = $db->getRows("
            SELECT COUNT( `c`.`id` ) AS `count`, `persons`, `run`, `score`
            FROM `competitions` `c`
            LEFT JOIN `score_types` `t` ON `c`.`score_type_id` = `t`.`id`
            WHERE `c`.`id`
            IN (
                    SELECT `s`.`competition_id`
                    FROM `x_scores_hl` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    WHERE `c`.`event_id` = '".$db->escape($_GET['event'])."'
                    GROUP BY `s`.`competition_id`
                UNION
                    SELECT `s`.`competition_id`
                    FROM `x_scores_hbm` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    WHERE `c`.`event_id` = '".$db->escape($_GET['event'])."'
                    GROUP BY `s`.`competition_id`
                UNION
                    SELECT `s`.`competition_id`
                    FROM `x_scores_hbf` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    WHERE `c`.`event_id` = '".$db->escape($_GET['event'])."'
                    GROUP BY `s`.`competition_id`
            )
            GROUP BY `t`.`id`
            ORDER BY `persons`, `run`, `score`
        ");
    } elseif (Check::get('place') && Check::isIn($_GET['place'], 'places')) {
        $types = $db->getRows("
            SELECT COUNT( `c`.`id` ) AS `count`, `persons`, `run`, `score`
            FROM `competitions` `c`
            LEFT JOIN `score_types` `t` ON `c`.`score_type_id` = `t`.`id`
            WHERE `c`.`id`
            IN (
                    SELECT `s`.`competition_id`
                    FROM `x_scores_hl` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    WHERE `c`.`place_id` = '".$db->escape($_GET['place'])."'
                    GROUP BY `s`.`competition_id`
                UNION
                    SELECT `s`.`competition_id`
                    FROM `x_scores_hbm` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    WHERE `c`.`place_id` = '".$db->escape($_GET['place'])."'
                    GROUP BY `s`.`competition_id`
                UNION
                    SELECT `s`.`competition_id`
                    FROM `x_scores_hbf` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    WHERE `c`.`place_id` = '".$db->escape($_GET['place'])."'
                    GROUP BY `s`.`competition_id`
            )
            GROUP BY `t`.`id`
            ORDER BY `persons`, `run`, `score`
        ");
    } else {

        $types = $db->getRows("
            SELECT COUNT( `c`.`id` ) AS `count`, `persons`, `run`, `score`
            FROM `competitions` `c`
            LEFT JOIN `score_types` `t` ON `c`.`score_type_id` = `t`.`id`
            WHERE `c`.`id`
            IN (
                    SELECT `competition_id`
                    FROM `x_scores_hl`
                    GROUP BY `competition_id`
                UNION
                    SELECT `competition_id`
                    FROM `x_scores_hbm`
                    GROUP BY `competition_id`
                UNION
                    SELECT `competition_id`
                    FROM `x_scores_hbf`
                    GROUP BY `competition_id`
            )
            GROUP BY `t`.`id`
            ORDER BY `persons`, `run`, `score`
        ");
    }
    
    $labels = array();
    $counts = array();

    foreach ($types as $type) {
        if (!$type['persons']) {
            $labels[] = 'Keine';
        } else {
            $labels[] = $type['persons'].'/'.$type['run'].'/'.$type['score'];
        }
        $counts[] = $type['count'];
    }

    $MyData = new pData();
    $MyData->addPoints($counts, "time");
    $MyData->addPoints($labels, "Platzierung");
    $MyData->setAbscissa("Platzierung");

    $fullData = array(
        'myData' => $MyData
    );
    Cache::put($fullData);
}

$MyData = $fullData['myData'];


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
    $h = 110;
    $myPicture = Chart::create($w, $h, $MyData);

    /* Turn of Antialiasing */
    $myPicture->Antialias = TRUE;

    /* Set the default font */
    $myPicture->setFontProperties(array(
        "FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf",
        "FontSize"=>Chart::size(9),
        "R"=>0,
        "G"=>0,
        "B"=>0
    ));

    /* Create the pPie object */
    $PieChart = new pPie($myPicture, $MyData);

    /* Draw a simple pie chart */
    $PieChart->draw2DPie(Chart::size(50),Chart::size(50), array(
        "WriteValues"=>PIE_VALUE_PERCENTAGE,
        "ValueR"=>50,
        "ValueG"=>50,
        "ValueB"=>50,
        "ValueAlpha"=>100,
        "Border"=>TRUE,
        "ValuePosition"=>PIE_VALUE_INSIDE,
        "SkewFactor"=>0.5,
        "Radius"=>Chart::size(49),
        "ValuePadding"=>Chart::size(18),
        "LabelStacked"=>true
    ));

    $PieChart->drawPieLegend(Chart::size(98),Chart::size(17));

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
