<?php

$competitionId = Check2::except()->get('a')->isIn('competitions');
$discipline = Check2::except()->get('b')->fullKey('full');

if ($discipline === 'full') {
  $good = $db->getFirstRow("
    SELECT COUNT(*) AS `good`
    FROM (
      SELECT `id`
      FROM `scores`
      WHERE `competition_id` = '".$db->escape($competitionId)."'
      AND `time` IS NOT NULL
    UNION
      SELECT `id`
      FROM `scores_gs`
      WHERE `competition_id` = '".$db->escape($competitionId)."'
      AND `time` IS NOT NULL
    UNION
      SELECT `id`
      FROM `scores_la`
      WHERE `competition_id` = '".$db->escape($competitionId)."'
      AND `time` IS NOT NULL
    UNION
      SELECT `id`
      FROM `scores_fs`
      WHERE `competition_id` = '".$db->escape($competitionId)."'
      AND `time` IS NOT NULL
    ) `i`
  ", 'good');

  $bad = $db->getFirstRow("
    SELECT COUNT(*) AS `bad`
    FROM (
      SELECT `id`
      FROM `scores`
      WHERE `competition_id` = '".$db->escape($competitionId)."'
      AND `time` IS NULL
    UNION
      SELECT `id`
      FROM `scores_gs`
      WHERE `competition_id` = '".$db->escape($competitionId)."'
      AND `time` IS NULL
    UNION
      SELECT `id`
      FROM `scores_la`
      WHERE `competition_id` = '".$db->escape($competitionId)."'
      AND `time` IS NULL
    UNION
      SELECT `id`
      FROM `scores_fs`
      WHERE `competition_id` = '".$db->escape($competitionId)."'
      AND `time` IS NULL
    ) `i`
  ", 'bad');
} else {
  switch ($discipline['key']) {
    case 'gs':
    case 'la':
    case 'fs':
      $good = $db->getFirstRow("
        SELECT COUNT(*) AS `good`
        FROM `scores_".$discipline['key']."`
        WHERE `competition_id` = '".$db->escape($competitionId)."'
        ".($discipline['sex'] ? " AND `sex` = '".$discipline['sex']."' " : '')."
        AND `time` IS NOT NULL
      ", 'good');
      $bad = $db->getFirstRow("
        SELECT COUNT(*) AS `bad`
        FROM `scores_".$discipline['key']."`
        WHERE `competition_id` = '".$db->escape($competitionId)."'
        ".($discipline['sex'] ? " AND `sex` = '".$discipline['sex']."' " : '')."
        AND `time` IS NULL
      ", 'bad');
      break;

    case 'hb':
    case 'hl':
      $good = $db->getFirstRow("
        SELECT COUNT(*) AS `good`
        FROM `scores` `s`
        JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
        WHERE `time` IS NOT NULL
        ".($discipline['sex'] ? " AND `sex` = '".$discipline['sex']."' " : '')."
        AND `s`.`discipline` = '".$discipline['key']."'
        AND `s`.`team_number` ".($discipline['final'] ? "=" : ">")." -2
        AND `s`.`competition_id` = '".$db->escape($competitionId)."'
      ", 'good');
      $bad = $db->getFirstRow("
        SELECT COUNT(*) AS `bad`
        FROM `scores` `s`
        JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
        WHERE `time` IS NULL
        ".($discipline['sex'] ? " AND `sex` = '".$discipline['sex']."' " : '')."
        AND `s`.`discipline` = '".$discipline['key']."'
        AND `s`.`team_number` ".($discipline['final'] ? "=" : ">")." -2
        AND `s`.`competition_id` = '".$db->escape($competitionId)."'
      ", 'bad');
      break;
  }
}

$myData = new pData();
$myData->addPoints(array($good, $bad), "time");
$myData->addPoints(array('Gültig', 'Ungültig'), "Platzierung");
$myData->setAbscissa("Platzierung");

$myPicture = Chart::create(140, 65, $myData);
$myPicture->Antialias = TRUE;
$myPicture->setFontProperties(array(
  "FontName" => PCHARTDIR."fonts/UbuntuMono-R.ttf",
  "FontSize" => Chart::size(9),
  "R"        => 0,
  "G"        => 0,
  "B"        => 0
));

$pieChart = new pPie($myPicture, $myData);
$pieChart->draw2DPie(Chart::size(30), Chart::size(30), array(
  "WriteValues"   => PIE_VALUE_PERCENTAGE,
  "ValueR"        => 50,
  "ValueG"        => 50,
  "ValueB"        => 50,
  "ValueAlpha"    => 100,
  "Border"        => TRUE,
  "ValuePosition" => PIE_VALUE_INSIDE,
  "SkewFactor"    => 0.5,
  "Radius"        => Chart::size(30),
  "ValuePadding"  => Chart::size(15),
  "LabelStacked"  => true
));
$pieChart->drawPieLegend(Chart::size(68), Chart::size(17));

$myPicture->stroke();
