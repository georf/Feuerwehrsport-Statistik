<?php

if (!Check::get('id') || !Check::isIn($_GET['id'], 'teams'))  throw new Exception('bad input');

$_id = $_GET['id'];
$id = $_id;


$MyData = Cache::get();

if (!$MyData) {

    $members = array();

    // Hindernisbahn
    $scores = $db->getRows("
        SELECT `person_id`
        FROM `scores`
        WHERE `team_id` = '".$id."'
        AND `discipline` = 'HB'
    ");
    foreach ($scores as $score) {
        $pid = $score['person_id'];
        if (!isset($members[$pid])) $members[$pid] = 'male';
    }



    // Hakenleiter
    $scores = $db->getRows("
        SELECT `person_id`
        FROM `scores`
        WHERE `team_id` = '".$id."'
        AND `discipline` = 'HL'
    ");
    foreach ($scores as $score) {
        $pid = $score['person_id'];
        if (!isset($members[$pid])) $members[$pid] = 'male';
    }




    // Gruppenstafette
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`
        FROM `scores_gruppenstafette`
        WHERE `team_id` = '".$id."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 6; $i++) {

            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = 'male';
        }
    }




    // Löschangriff
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`
        FROM `scores_loeschangriff`
        WHERE `team_id` = '".$id."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 7; $i++) {

            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = 'male';
        }
    }




    // Feuerwehrstafette
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`
        FROM `scores_stafette`
        WHERE `team_id` = '".$id."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 7; $i++) {

            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = 'male';
        }
    }



    $memberships = $db->getRows("
      SELECT `person_id`, `start`, `end`, `id`
      FROM `team_memberships`
      WHERE `team_id` = '".$id."'
      GROUP BY `person_id`
    ");

    foreach ($memberships as $score) {
        $pid = $score['person_id'];
        if (!isset($members[$pid])) $members[$pid] = 'male';
    }

    $male = 0;
    $female = 0;
    foreach ($members as $pid=>$member) {
        $m = $db->getFirstRow("
            SELECT `sex`
            FROM `persons`
            WHERE `id` = '".$pid."'
            LIMIT 1;
        ");
        if ($m['sex'] == 'male') {
            $male++;
        } else {
            $female++;
        }
    }

    $MyData = new pData();
    $MyData->addPoints(array($male, $female), "time");
    $MyData->addPoints(array('Männlich', 'Weiblich'), "Platzierung");
    $MyData->setAbscissa("Platzierung");

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
