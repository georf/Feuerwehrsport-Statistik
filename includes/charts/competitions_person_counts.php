<?php

// a == id
// b == name

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (Check::get('b')) $_GET['name'] = $_GET['b'];

if (Check::get('name', 'id') && $_GET['name'] == 'event' && Check::isIn($_GET['id'], 'events')) {
    $competitions = $db->getRows("
        SELECT `id`
        FROM `competitions`
        WHERE `event_id` = '".$db->escape($_GET['id'])."'
    ");
} elseif (Check::get('name', 'id') && $_GET['name'] == 'place' && Check::isIn($_GET['id'], 'places')) {
    $competitions = $db->getRows("
        SELECT `id`
        FROM `competitions`
        WHERE `place_id` = '".$db->escape($_GET['id'])."'
    ");
} elseif (Check::get('name', 'id') && $_GET['name'] == 'year' && is_numeric($_GET['id'])) {
    $competitions = $db->getRows("
        SELECT `id`
        FROM `competitions`
        WHERE YEAR(`date`) = '".$db->escape($_GET['id'])."'
    ");
} else {
    $competitions = $db->getRows("
        SELECT `id`
        FROM `competitions`
    ");
}

$counts = array( 0, 0, 0, 0, 0, 0);
$labels = array(
    'Keine',
    '1 bis 25',
    '25 bis 50',
    '50 bis 75',
    '75 bis 100',
    '> 100'
);

foreach ($competitions as $competition) {

    $count = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM (
            SELECT `person_id`
            FROM `scores`
            WHERE `competition_id` = '".$competition['id']."'
            GROUP BY `person_id`
        ) `i`
    ", 'count');

    if ($count <= 0) $counts[0]++;
    elseif ($count < 26) $counts[1]++;
    elseif ($count < 51) $counts[2]++;
    elseif ($count < 76) $counts[3]++;
    elseif ($count < 101) $counts[4]++;
    else $counts[5]++;
}

$MyData = new pData();
$MyData->addPoints($counts, "time");
$MyData->addPoints($labels, "Platzierung");
$MyData->setAbscissa("Platzierung");

$w = 176;
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

/* Render the picture */
$myPicture->stroke();
