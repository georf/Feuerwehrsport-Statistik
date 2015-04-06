<?php

// a = dis
// b = id

$discipline = Check2::except()->get('a')->isDiscipline();
$person = Check2::except()->get('b')->isIn('persons', 'row');

$positions = array();
$labels    = array();
$show      = array();
$all       = 0;

for ($i = 1; $i <= WK::count($discipline); $i++) {
  $count = $db->getFirstRow("
    SELECT COUNT(`pp`.`id`) AS `count`
    FROM `person_participations` `pp`
    INNER JOIN `group_scores` `gs` ON `pp`.`score_id` = `gs`.`id`
    INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
    INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
    WHERE `pp`.`person_id` = ".$person['id']."
    AND `pp`.`position` = ".$i."
    AND `gst`.`discipline` = '".$discipline."'
  ", 'count');
  
  $positions[] = $count;
  if ($count > 0) {
    $show[]   = $count;
    $labels[] = WK::type($i, $person['sex'], $discipline);
    $all     += $count;
  }
}

$MyData = new pData();
$MyData->addPoints($show, "time");
$MyData->addPoints($labels, "labels");
$MyData->setAbscissa("labels");

if ($discipline == 'la') {
  $w = 920;
  $h = 155;
  $pie = array(750, 72);
  $legend = array(795, 17);
} else {
  $w = 150;
  $h = 90;
  $pie = array(38, 38);
  $legend = array(76, 15);
}

/* Create the pChart object */
$myPicture = new pImage($w, $h, $MyData, TRUE);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>9,"R"=>0,"G"=>0,"B"=>0));

$myPicture->setShadow(FALSE);

if ($discipline == 'la') {
  $myPicture->drawFromPNG(0,0, __DIR__."/images/la.png");
  if ($all != 0) {
    $wks = array(
      array(77,68),
      array(77,45),
      array(77,25),
      array(295,82),
      array(630,35),
      array(459,77),
      array(630,119),
    );

    foreach ($wks as $i => $wk) {
      if ($positions[$i] == 0) continue;

      $pro = $positions[$i]/$all;

      $myPicture->drawFilledCircle($wk[0],$wk[1],30*$pro+8, array("R"=>104,"G"=>245, "B"=>63,"Alpha"=>30*$pro+60));
    }
  }
}

/* Create the pPie object */
$PieChart = new pPie($myPicture, $MyData);
/* Draw a simple pie chart */
$PieChart->draw2DPie(Chart::size($pie[0]),Chart::size($pie[1]), array(
    "WriteValues"=>PIE_VALUE_PERCENTAGE,
    "ValueR"=>50,
    "ValueG"=>50,
    "ValueB"=>50,
    "ValueAlpha"=>100,
    "Border"=>TRUE,
    "ValuePosition"=>PIE_VALUE_INSIDE,
    "SkewFactor"=>0.5,
    "Radius"=>Chart::size(38),
    "ValuePadding"=>Chart::size(15),
    "LabelStacked"=>true
));

$PieChart->drawPieLegend(Chart::size($legend[0]),Chart::size($legend[1]));

/* Render the picture */
$myPicture->stroke();
