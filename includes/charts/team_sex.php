<?php

// a == id

if (Check::get('a')) $_GET['id'] = $_GET['a'];

if (!Check::get('id') || !Check::isIn($_GET['id'], 'teams'))  throw new Exception('bad input');

$_id = $_GET['id'];
$id = $_id;

$sexes = $db->getRows("
  SELECT `sex`
  FROM
   (
      SELECT `person_id`
      FROM `scores`
      WHERE `team_id` = '".$id."'
    UNION
      SELECT `p`.`person_id`
      FROM `scores_gs` `s`
      INNER JOIN `person_participations_gs` `p` ON `p`.`score_id` = `s`.`id`
      WHERE `s`.`team_id` = '".$id."'
      AND `time` IS NULL
    UNION
      SELECT `p`.`person_id`
      FROM `scores_la` `s`
      INNER JOIN `person_participations_la` `p` ON `p`.`score_id` = `s`.`id`
      WHERE `s`.`team_id` = '".$id."'
      AND `time` IS NULL
    UNION
      SELECT `p`.`person_id`
      FROM `scores_fs` `s`
      INNER JOIN `person_participations_fs` `p` ON `p`.`score_id` = `s`.`id`
      WHERE `s`.`team_id` = '".$id."'
      AND `time` IS NULL
    ) `i`
  INNER JOIN `persons` `p` ON `p`.`id` = `i`.`person_id`
", 'sex');

$male = 0;
$female = 0;
foreach ($sexes as $sex) {
  if ($sex == 'male') {
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
