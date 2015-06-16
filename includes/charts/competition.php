<?php

$competition = Check2::except()->get('a')->isIn('competitions', 'row');
$discipline = Check2::except()->get('b')->fullKey();
$categoryId = Check2::value()->get('c')->isIn('group_score_categories');
$calculation = CalculationCompetition::build($competition);
$groupScores = $calculation->scores($discipline);

$scores = array();
foreach ($groupScores as $gScore) {
  if ($gScore->categoryId() == $categoryId) {
    $scores = $gScore->scores();
  }
}


$points = array();
$labels = array();
$i = 1;
foreach ($scores as $score) {
  if (FSS::isInvalid($score['time'])) continue;
  $points[] = intval($score['time'])/100;
  $labels[] = $i.'.';
  $i++;
}
$myData = new pData();
$myData->addPoints($points, "time");

if ($discipline['key'] == 'zk') {
  $hl = array();
  $hb = array();
  foreach ($scores as $score) {
    if (FSS::isInvalid($score['time'])) continue;
    $hl[] = intval($score['hl'])/100;
    $hb[] = intval($score['hb'])/100;
  }
  $myData->addPoints($hl, "HL");
  $myData->addPoints($hb, "HB");
}
$myData->addPoints($labels, "Platzierung");
$myData->setAbscissa("Platzierung");
$myData->setSerieDescription("time", 'Zeit - Platzierung');

$myPicture = Chart::create(700, 230, $myData);
$myPicture->Antialias = false;
$myPicture->setFontProperties(array(
  "FontName" => PCHARTDIR."fonts/UbuntuMono-R.ttf",
  "FontSize" => Chart::size(7),
  "R"        => 0,
  "G"        => 0,
  "B"        => 0
));
$myPicture->setGraphArea(Chart::size(40), Chart::size(30), Chart::size(660), Chart::size(200));
$myPicture->drawScale(array(
  "XMargin"           => Chart::size(10),
  "YMargin"           => Chart::size(10),
  "Floating"          => TRUE,
  "GridR"             => 211,
  "GridG"             => 214,
  "GridB"             => 255,
  "GridAlpha"         => 50,
  "BackgroundR2"      => 226,
  "BackgroundG2"      => 228,
  "BackgroundB2"      => 255,
  "BackgroundAlpha2"  => 50,
  "DrawSubTicks"      => TRUE,
  "CycleBackground"   => TRUE,
  "LabelSkip"         => min(4, max(0, ceil($myData->getSerieCount('time')/10) - 1))
));
$myPicture->Antialias = true;
$myPicture->setShadow(true, array(
  "X"     => 1,
  "Y"     => 1,
  "R"     => 0,
  "G"     => 0,
  "B"     => 0,
  "Alpha" => 10
));

$myPicture->drawLineChart();
$myPicture->drawPlotChart(array(
  "PlotSize"      => 1,
  "DisplayValues" => false,
  "PlotBorder"    => true,
  "BorderSize"    => 1,
  "Surrounding"   => -50,
  "BorderAlpha"   => 80
));

$myPicture->drawLegend(Chart::size(500), Chart::size(10), array(
  "Style"    => LEGEND_NOBORDER,
  "Mode"     => LEGEND_HORIZONTAL,
  "FontR"    => 0,
  "FontG"    => 0,
  "FontB"    => 0,
  "FontName" => PCHARTDIR."fonts/calibri.ttf",
  "FontSize" => Chart::size(10)
));

$myPicture->drawThreshold($myData->getSerieAverage("time"), array(
  "WriteCaption" => true,
  "Caption"      => "Durchscnnitt",
  "CaptionAlign" => CAPTION_RIGHT_BOTTOM
));

$myPicture->stroke();
