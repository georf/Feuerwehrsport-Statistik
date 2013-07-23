<?php

// a == id

if (Check::get('a')) $_GET['id'] = $_GET['a'];

if (!Check::get('id') || !Check::isIn($_GET['id'], 'teams'))  throw new Exception('bad input');

$_id = $_GET['id'];
$id = $_id;

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
    FROM `scores_gs`
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
    FROM `scores_la`
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
    FROM `scores_fs`
    WHERE `team_id` = '".$id."'
");
foreach ($scores as $score) {
    for($i = 1; $i <= 7; $i++) {

        if (empty($score['person_'.$i])) continue;

        $pid = $score['person_'.$i];
        if (!isset($members[$pid])) $members[$pid] = 'male';
    }
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


$w = 140;
$h = 65;
$title = '';



$myPicture = Chart::create($w, $h, $MyData);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>Chart::size(9),"R"=>0,"G"=>0,"B"=>0));

/* Create the pPie object */
$PieChart = new pPie($myPicture,$MyData);

$PieChart->setSliceColor(0, FSS::palette('male'));
$PieChart->setSliceColor(1, FSS::palette('female'));

/* Draw a simple pie chart */
$PieChart->draw2DPie(Chart::size(30),Chart::size(30),array(
    "WriteValues"=>PIE_VALUE_PERCENTAGE,
    "ValueR"=>50,
    "ValueG"=>50,
    "ValueB"=>50,
    "ValueAlpha"=>100,
    "Border"=>TRUE,
    "ValuePosition"=>PIE_VALUE_INSIDE,
    "SkewFactor"=>0.5,
    "Radius"=>Chart::size(30),
    "ValuePadding"=>Chart::size(15)));

$PieChart->drawPieLegend(Chart::size(68),Chart::size(17));

/* Render the picture */
$myPicture->stroke();