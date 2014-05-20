<?php

// a = id

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (!Check::get('id') || !Check::isIn($_GET['id'], 'persons'))  throw new Exception('bad input');

$_id = $_GET['id'];
$id = $_id;

$person = FSS::tableRow('persons', $id);

$positions = array();
$labels = array();
for ($i = 1; $i < 5; $i++) {
    $positions[] = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `person_participations_fs`
        WHERE `person_id` = ".$id."
        AND `position` = ".$i."
    ", 'count');
}

$show = array();
for ($i = 1; $i < 5; $i++) {
    if ($positions[$i - 1] != 0) {
        $show[] = $positions[$i - 1];
        $labels[] = WK::type($i, $person['sex'], 'fs');
    }
}

$MyData = new pData();
$MyData->addPoints($show, "time");
$MyData->addPoints($labels, "labels");
$MyData->setAbscissa("labels");

$all = 0;
foreach ($positions as $p) {
    $all += $p;
}

$w = 150;
$h = 90;

/* Create the pChart object */
$myPicture = new pImage($w, $h, $MyData, TRUE);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>9,"R"=>0,"G"=>0,"B"=>0));

$myPicture->setShadow(FALSE);


/* Create the pPie object */
$PieChart = new pPie($myPicture, $MyData);
/* Draw a simple pie chart */
$PieChart->draw2DPie(Chart::size(38),Chart::size(38), array(
    "WriteValues"=>PIE_VALUE_PERCENTAGE,
    "ValueR"=>50,
    "ValueG"=>50,
    "ValueB"=>50,
    "ValueAlpha"=>100,
    "Border"=>TRUE,
    "ValuePosition"=>PIE_VALUE_INSIDE,
    "SkewFactor"=>0.5,
    "Radius"=>Chart::size(38),
    "ValuePadding"=>Chart::size(10),
    "LabelStacked"=>true
));

$PieChart->drawPieLegend(Chart::size(76),Chart::size(15));

/* Render the picture */
$myPicture->stroke();
