<?php

// a == id
// b == name

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (Check::get('b')) $_GET['name'] = $_GET['b'];

$labels = array(
    'Alle',
    'Nur HL',
    'Nur HB',
    'Nur LA',
    'Andere',
);
$counts = array(0,0,0,0,0);


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
} else {
    $competitions = $db->getRows("
        SELECT `id`
        FROM `competitions`
    ");
}

foreach ($competitions as $competition) {

    $hbm = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_hbm`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $hb = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_hbf`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count') + $hbm;
    $gs = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_gs`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $la = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_la`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $fs = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores_fs`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');
    $hl = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `x_scores_hl`
        WHERE `competition_id` = '".$competition['id']."'
    ", 'count');

    if ($hb > 0 && $gs > 0 && $la > 0 && $fs > 0 && $hl > 0) {
        $counts[0]++;
    } elseif ($hl > 0 && $gs + $la + $fs + $hb == 0) {
        $counts[1]++;
    } elseif ($hb > 0 && $gs + $la + $fs + $hl == 0) {
        $counts[2]++;
    } elseif ($la > 0 && $gs + $hl + $fs + $hb == 0) {
        $counts[3]++;
    } else {
        $counts[4]++;
    }
}

$MyData = new pData();
$MyData->addPoints($counts, "time");
$MyData->addPoints($labels, "Platzierung");
$MyData->setAbscissa("Platzierung");

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

/* Render the picture */
$myPicture->stroke();
