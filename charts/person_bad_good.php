<?php


$fullData = Cache::get();

if (!$fullData) {
    if (!Check::get('id', 'key')) throw new Exception('not enough arguments');
    if (!Check::isIn($_GET['id'], 'persons')) throw new Exception('bad person');
    $id = intval($_GET['id']);
    $key = $_GET['key'];

    switch ($key) {

        case 'full':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM (
                    SELECT `id`
                    FROM `scores_gs`
                    WHERE `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                    AND `time` IS NOT NULL
                UNION
                    SELECT `id`
                    FROM `scores_la`
                    WHERE `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                    OR `person_7` = '".$id."'
                    AND `time` IS NOT NULL
                UNION
                    SELECT `id`
                    FROM `scores_fs`
                    WHERE `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    AND `time` IS NOT NULL
                UNION
                    SELECT `id`
                    FROM `scores`
                    WHERE `time` IS NOT NULL
                    AND `person_id` = '".$id."'
                ) `i`
            ", 'good');

            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM (
                    SELECT `id`
                    FROM `scores_gs`
                    WHERE `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                    AND `time` IS NULL
                UNION
                    SELECT `id`
                    FROM `scores_la`
                    WHERE `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                    OR `person_7` = '".$id."'
                    AND `time` IS NULL
                UNION
                    SELECT `id`
                    FROM `scores_fs`
                    WHERE `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    AND `time` IS NULL
                UNION
                    SELECT `id`
                    FROM `scores`
                    WHERE `time` IS NULL
                    AND `person_id` = '".$id."'
                ) `i`
            ", 'bad');
            $title = 'Ganzer Wettkampf';

            break;

        case 'gs':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores_gs`
                WHERE `time` IS NOT NULL
                AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                )
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores_gs`
                WHERE `time` IS NULL
                AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                )
            ", 'bad');
            $title = FSS::dis2name($key);
            break;

        case 'la':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores_la`
                WHERE `time` IS NOT NULL
                AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                    OR `person_7` = '".$id."'
                )
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores_la`
                WHERE `time` IS NULL
                AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                    OR `person_5` = '".$id."'
                    OR `person_6` = '".$id."'
                    OR `person_7` = '".$id."'
                )
            ", 'bad');
            $title = FSS::dis2name($key);
            break;

        case 'fs':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores_fs`
                WHERE `time` IS NOT NULL
                AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                )
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores_fs`
                WHERE `time` IS NULL
                AND (
                    `person_1` = '".$id."'
                    OR `person_2` = '".$id."'
                    OR `person_3` = '".$id."'
                    OR `person_4` = '".$id."'
                )
            ", 'bad');
            $title = FSS::dis2name($key);
            break;

        case 'hb':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores`
                WHERE `time` IS NOT NULL
                AND `person_id` = '".$id."'
                AND `discipline` = 'HB'
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores`
                WHERE `time` IS NULL
                AND `person_id` = '".$id."'
                AND `discipline` = 'HB'
            ", 'bad');
            $title = FSS::dis2name($key);
            break;

        case 'hl':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores`
                WHERE `time` IS NOT NULL
                AND `person_id` = '".$id."'
                AND `discipline` = 'HL'
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores`
                WHERE `time` IS NULL
                AND `person_id` = '".$id."'
                AND `discipline` = 'HL'
            ", 'bad');
            $title = FSS::dis2name($key);
            break;

        default:
            throw new Exception('bad key');
            break;
    }

    $MyData = new pData();
    $MyData->addPoints(array($good, $bad), "time");
    $MyData->addPoints(array('Gültig', 'Ungültig'), "Platzierung");
    $MyData->setAbscissa("Platzierung");

    $fullData = array(
        'title' => $title,
        'myData' => $MyData
    );
    Cache::put($fullData);
}

$MyData = $fullData['myData'];
$title = $fullData['title'];


/* Create the cache object */
$MyCache = new pCache();

/* Compute the hash linked to the chart data */
$ChartHash = $MyCache->getHash($MyData, Cache::getId());


/* Test if we got this hash in our cache already */
if ( $MyCache->isInCache($ChartHash)) {

    /* If we have it, get the picture from the cache! */
    $MyCache->strokeFromCache($ChartHash);
} else {



    $w = 140;
    $h = 65;
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
    $PieChart->draw2DPie(Chart::size(30),Chart::size(30), array(
        "WriteValues"=>PIE_VALUE_PERCENTAGE,
        "ValueR"=>50,
        "ValueG"=>50,
        "ValueB"=>50,
        "ValueAlpha"=>100,
        "Border"=>TRUE,
        "ValuePosition"=>PIE_VALUE_INSIDE,
        "SkewFactor"=>0.5,
        "Radius"=>Chart::size(30),
        "ValuePadding"=>Chart::size(15),
        "LabelStacked"=>true
    ));

    $PieChart->drawPieLegend(Chart::size(68),Chart::size(17));

    /* Push the rendered picture to the cache */
    $MyCache->writeToCache($ChartHash, $myPicture);

    /* Render the picture */
    $myPicture->stroke();
}
