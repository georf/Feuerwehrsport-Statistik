<?php

// a == id
// b == name

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

foreach ($competitions as $competition) {
  $c = array();
  foreach (FSS::$disciplines as $discipline) {
    if (FSS::isSingleDiscipline($discipline)) {
      $c[$discipline] = $db->getFirstRow("
        SELECT COUNT(`id`) AS `count`
        FROM `scores`
        WHERE `competition_id` = '".$competition['id']."'
        AND `discipline` = '".$discipline."'
      ", 'count');
    } else {
      $c[$discipline] = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `group_scores` `gs`
        INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
        INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
        WHERE `gsc`.`competition_id` = '".$competition['id']."'
        AND `gst`.`discipline` = '".$discipline."'
      ", 'count');
    }
  }

  if ($c['hb'] > 0 && $c['gs'] > 0 && $c['la'] > 0 && $c['fs'] > 0 && $c['hl'] > 0) {
    $counts[0]++;
  } elseif ($c['hl'] > 0 && $c['gs'] + $c['la'] + $c['fs'] + $c['hb'] == 0) {
    $counts[1]++;
  } elseif ($c['hb'] > 0 && $c['gs'] + $c['la'] + $c['fs'] + $c['hl'] == 0) {
    $counts[2]++;
  } elseif ($c['la'] > 0 && $c['gs'] + $c['hl'] + $c['fs'] + $c['hb'] == 0) {
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
