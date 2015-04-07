<?php

$teamId = Check2::except()->get('a')->isIn('teams');
$typeId = Check2::except()->get('b')->isIn('group_score_types');
$sex    = Check2::except()->get('c')->isSex();

$good = $db->getFirstRow("
  SELECT COUNT(*) AS `good`
  FROM `group_scores` `gs` 
  INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
  INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
  INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `gsc`.`competition_id`
  WHERE `gs`.`team_id` = '".$teamId."'
  AND `gst`.`id` = '".$typeId."'
  AND `gs`.`sex` = '".$sex."'
  AND `time` IS NOT NULL
", 'good');
$bad = $db->getFirstRow("
  SELECT COUNT(*) AS `bad`
  FROM `group_scores` `gs` 
  INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
  INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
  INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `gsc`.`competition_id`
  WHERE `gs`.`team_id` = '".$teamId."'
  AND `gst`.`id` = '".$typeId."'
  AND `gs`.`sex` = '".$sex."'
  AND `time` IS NULL
", 'bad');

$MyData = new pData();
$MyData->addPoints(array($good, $bad), "time");
$MyData->addPoints(array('Gültig', 'Ungültig'), "Platzierung");
$MyData->setAbscissa("Platzierung");

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

/* Render the picture */
$myPicture->stroke();

