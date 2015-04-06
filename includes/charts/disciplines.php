<?php

$points = array();
$labels = array();

foreach (FSS::$disciplines as $discipline) {
  if (FSS::isSingleDiscipline($discipline)) {
    $points[] = $db->getFirstRow("
      SELECT COUNT(*) AS `count`
      FROM `scores`
      WHERE `discipline` = '".$discipline."'
    ", 'count');
  } else {
    $points[] = $db->getFirstRow("
      SELECT COUNT(*) AS `count`
      FROM `group_scores` `gs`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `gst`.`discipline` = '".$discipline."'
    ", 'count');
  }
  $labels[] = strtoupper($discipline);
}

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
