<?php


$teamId = Check2::except()->get('a')->isIn('teams');
$typeId = Check2::except()->get('b')->isIn('group_score_types');
$sex    = Check2::except()->get('c')->isSex();

$scores = $db->getRows("
  SELECT `g`.`time`,`c`.`date` AS `date`
  FROM (
    SELECT *
    FROM
    (
      SELECT `competition_id`,`time`
      FROM `group_scores` `gs` 
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `time` IS NOT NULL
      AND `gs`.`team_id` = '".$teamId."'
      AND `gst`.`id` = '".$typeId."'
      AND `gs`.`sex` = '".$sex."'
      ORDER BY `time`
    ) `i`
    GROUP BY `competition_id`
  ) `g`

  INNER JOIN `competitions` `c` ON `c`.`id` = `g`.`competition_id`
  ORDER BY `c`.`date`
");

$points = array();
$labels = array();
$i = 1;
foreach ($scores as $score) {
  $points[] = intval($score['time'])/100;
  $labels[] = gDate($score['date']);
  $i++;
}

$MyData = new pData();
$MyData->addPoints($points, "time");

$MyData->addPoints($labels, "Daten");
$MyData->setAbscissa("Daten");
$MyData->setSerieDescription("time", 'Zeit');

$myPicture = Chart::create(700, 210, $MyData);

/* Turn of Antialiasing */
$myPicture->Antialias = FALSE;

/* Set the default font */
$myPicture->setFontProperties(array(
  "FontName" => PCHARTDIR."fonts/UbuntuMono-R.ttf",
  "FontSize" => Chart::size(7),
  "R"        => 0,
  "G"        => 0,
  "B"        => 0
));

/* Define the chart area */
$myPicture->setGraphArea(Chart::size(30),Chart::size(1),Chart::size(694),Chart::size(150));

/* Draw the scale */
$scaleSettings = array(
  "XMargin"=>Chart::size(10),
  "YMargin"=>Chart::size(10),
  "Floating"=>TRUE,
  "GridR"=>200,
  "GridG"=>200,
  "GridB"=>200,
  "DrawSubTicks"=>TRUE,
  "CycleBackground"=>TRUE,
  "LabelRotation"=>90,
);
$myPicture->drawScale($scaleSettings);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the line chart */
$myPicture->drawLineChart();
$myPicture->drawPlotChart(array("PlotSize"=>1,"DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-50,"BorderAlpha"=>80));

/* Write the chart legend */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 $myPicture->drawLegend(Chart::size(640),Chart::size(20),array("Style"=>LEGEND_BOX,"BoxSize"=>4,"R"=>200,"G"=>200,"B"=>200,"Surrounding"=>20,"Alpha"=>30));


/* Draw the standard mean and the geometric one */
$Mean = $MyData->getSerieAverage("time");
$myPicture->drawThreshold($Mean,array("WriteCaption"=>TRUE,"Caption"=>"Durchscnnitt","CaptionAlign"=>CAPTION_RIGHT_BOTTOM));

/* Render the picture */
$myPicture->stroke();

