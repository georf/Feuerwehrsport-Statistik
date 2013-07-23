<?php

$points = array();
$labels = array();

$points[] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores`
    WHERE `discipline` = 'HL'
", 'count');
$labels[] = 'HL';

$points[] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores`
    WHERE `discipline` = 'HB'
", 'count');
$labels[] = 'HB';

$points[] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_la`
", 'count');
$labels[] = 'LA';

$points[] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_fs`
", 'count');
$labels[] = 'FS';

$points[] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_gs`
", 'count');
$labels[] = 'GS';

$MyData = new pData();
$MyData->addPoints($points, "time");
$MyData->addPoints($labels, "Platzierung");
$MyData->setAbscissa("Platzierung");

$w =120;
$h = 80;
$title = '';

/* Create the pChart object */
$myPicture = Chart::create($w, $h, $MyData);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>Chart::size(9),"R"=>0,"G"=>0,"B"=>0));

/* Create the pPie object */
$PieChart = new pPie($myPicture,$MyData);

/* Draw a simple pie chart */
$PieChart->draw2DPie(Chart::size(40),Chart::size(40),array(
    "WriteValues"=>PIE_VALUE_PERCENTAGE,
    "ValueR"=>50,
    "ValueG"=>50,
    "ValueB"=>50,
    "ValueAlpha"=>100,
    "Border"=>TRUE,
    "ValuePosition"=>PIE_VALUE_INSIDE,
    "SkewFactor"=>0.5,
    "Radius"=>Chart::size(40),
    "ValuePadding"=>Chart::size(15)));

$PieChart->drawPieLegend(Chart::size(78),Chart::size(10));

/* Render the picture */
$myPicture->stroke();