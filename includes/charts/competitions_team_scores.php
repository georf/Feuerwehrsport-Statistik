<?php

// a == id
// b == name

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (Check::get('b')) $_GET['name'] = $_GET['b'];

if (Check::get('name', 'id') && $_GET['name'] == 'event' && Check::isIn($_GET['id'], 'events')) {
  $types = $db->getRows("
    SELECT COUNT( `c`.`id` ) AS `count`, `persons`, `run`, `score`
    FROM `competitions` `c`
    LEFT JOIN `score_types` `t` ON `c`.`score_type_id` = `t`.`id`
    WHERE `c`.`id`
    IN (
      SELECT `s`.`competition_id`
      FROM `scores` `s`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE `c`.`event_id` = '".$db->escape($_GET['id'])."'
      GROUP BY `s`.`competition_id`
    )
    GROUP BY `t`.`id`
    ORDER BY `persons`, `run`, `score`
  ");
} elseif (Check::get('name', 'id') && $_GET['name'] == 'place' && Check::isIn($_GET['id'], 'places')) {
  $types = $db->getRows("
    SELECT COUNT( `c`.`id` ) AS `count`, `persons`, `run`, `score`
    FROM `competitions` `c`
    LEFT JOIN `score_types` `t` ON `c`.`score_type_id` = `t`.`id`
    WHERE `c`.`id`
    IN (
      SELECT `s`.`competition_id`
      FROM `scores` `s`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE `c`.`place_id` = '".$db->escape($_GET['id'])."'
      GROUP BY `s`.`competition_id`
    )
    GROUP BY `t`.`id`
    ORDER BY `persons`, `run`, `score`
  ");
} elseif (Check::get('name', 'id') && $_GET['name'] == 'year' && is_numeric($_GET['id'])) {
  $types = $db->getRows("
    SELECT COUNT( `c`.`id` ) AS `count`, `persons`, `run`, `score`
    FROM `competitions` `c`
    LEFT JOIN `score_types` `t` ON `c`.`score_type_id` = `t`.`id`
    WHERE `c`.`id`
    IN (
      SELECT `s`.`competition_id`
      FROM `scores` `s`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE YEAR(`c`.`date`) = '".$db->escape($_GET['id'])."'
      GROUP BY `s`.`competition_id`
    )
    GROUP BY `t`.`id`
    ORDER BY `persons`, `run`, `score`
  ");
} else {
  $types = $db->getRows("
    SELECT COUNT( `c`.`id` ) AS `count`, `persons`, `run`, `score`
    FROM `competitions` `c`
    LEFT JOIN `score_types` `t` ON `c`.`score_type_id` = `t`.`id`
    WHERE `c`.`id`
    IN (
      SELECT `competition_id`
      FROM `scores`
      GROUP BY `competition_id`
    )
    GROUP BY `t`.`id`
    ORDER BY `persons`, `run`, `score`
  ");
}

$labels = array();
$counts = array();

foreach ($types as $type) {
    if (!$type['persons']) {
        $labels[] = 'Keine';
    } else {
        $labels[] = $type['persons'].'/'.$type['run'].'/'.$type['score'];
    }
    $counts[] = $type['count'];
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

