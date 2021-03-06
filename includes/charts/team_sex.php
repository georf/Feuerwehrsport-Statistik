<?php

$teamId = Check2::except()->get('a')->isIn('teams');

$sexes = $db->getRows("
  SELECT `sex`
  FROM
   (
      SELECT `person_id`
      FROM `scores`
      WHERE `team_id` = '".$teamId."'
    UNION
      SELECT `p`.`person_id`
      FROM `group_scores` `gs`
      INNER JOIN `person_participations` `p` ON `p`.`score_id` = `gs`.`id`
      WHERE `gs`.`team_id` = '".$teamId."'
    ) `i`
  INNER JOIN `persons` `p` ON `p`.`id` = `i`.`person_id`
  GROUP BY `p`.`id`
", 'sex');

$male = 0;
$female = 0;
foreach ($sexes as $sex) {
  if ($sex == 'male')  $male++;
  else                 $female++;
}

$MyData = new pData();
$MyData->addPoints(array($male, $female), "time");
$MyData->addPoints(array('Männlich', 'Weiblich'), "Geschlechter");
$MyData->setAbscissa("Geschlechter");

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
