<?php

// a = id


if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (!Check::get('id')) throw new Exception('not enough arguments');
if (!Check::isIn($_GET['id'], 'persons')) throw new Exception('bad person');

$person = $db->getFirstRow("
    SELECT *
    FROM `persons`
    WHERE `id` = '".$db->escape($_GET['id'])."'
  ");

if (!$person) exit();
$_id = $person['id'];


$years = $db->getRows("
    SELECT YEAR(`date`) AS `year`
    FROM `competitions`
    GROUP BY YEAR(`date`)
    ORDER BY `year`
");

$yearRows = $db->getRows("
    SELECT YEAR(`c`.`date`) AS `year`
    FROM `scores` `s`
    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
    INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
    WHERE `s`.`person_id` = '".$_id."'
    GROUP BY `year`
    ORDER BY `year`
");

$years = array();
for ($i = $yearRows[0]['year']; $i <= $yearRows[count($yearRows) - 1]['year']; $i++) {
    $years[] = array('year'=>$i.'');
}



if ($person['sex'] == 'male') {
    $diss = array(array(
            'name' => 'HL',
            'dis' => 1,
            'avgs' => array()
        ), array(
            'name' => 'HB',
            'dis' => 2,
            'avgs' => array()
        )
    );
} else {
    $diss = array(array(
            'name' => 'HB',
            'dis' => 2,
            'avgs' => array()
        )
    );
}


$labels  = array();


foreach ($years as $year) {
    $labels[] = substr($year['year'],2);

    foreach ($diss as $key => $dis) {

        $avg = $db->getFirstRow("
            SELECT AVG(`i2`.`time`) AS `avg`
            FROM (
                SELECT *
                FROM (
                  SELECT `s`.`time`,`p`.`name` AS `place`,`e`.`name` AS `event`,`c`.`date`,`c`.`id`
                  FROM `scores` `s`
                  INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                  INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
                  INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
                  WHERE `s`.`person_id` = '".$db->escape($_id)."'
                  AND YEAR(`c`.`date`) = '".$year['year']."'
                  AND `s`.`discipline` = '".$db->escape($dis['name'])."'
                  AND `s`.`time` IS NOT NULL
                  ORDER BY `s`.`time`
                ) `i`
                GROUP BY `i`.`id`
              ) `i2`
        ", 'avg');


        if (is_numeric($avg)) {
            $diss[$key]['avgs'][] = c2s($avg);
        } else {
            $diss[$key]['avgs'][] = VOID;
        }

    }
}



$MyData = new pData();
$MyData->addPoints($labels, "Labels");
foreach ($diss as $key => $dis) {
    $MyData->addPoints($dis['avgs'], $dis['name']);
}
$MyData->setSerieDescription("Labels", "Months");
$MyData->setAbscissa("Labels");
$MyData->setAxisName(0,'Zeiten');

$w = 210;
$h = 150;

/* Create the pChart object */
$myPicture = new pImage($w, $h, $MyData, TRUE);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>8,"R"=>0,"G"=>0,"B"=>0));

/* Define the chart area */
$myPicture->setGraphArea(25,15,200,135);

/* Draw the scale */
$scaleSettings = array(
  "XMargin"=>0,
  "YMargin"=>0,
  "GridR"=>220,
  "GridG"=>220,
  "GridB"=>220,
  //"Mode" => SCALE_MODE_MANUAL,
  //"ManualScale" => array(array('Min'=>15, 'Max'=>21)),
  "CycleBackground"=>TRUE
);
$myPicture->drawScale($scaleSettings);

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the line chart */
$myPicture->drawLineChart(array(
    'BreakVoid' => false,
));
$myPicture->drawPlotChart(array(
    "PlotSize"=>1,
    "DisplayValues"=>FALSE,
    "PlotBorder"=>False,
    "BorderSize"=>1,
    "Surrounding"=>-50,
    "BorderAlpha"=>80
));


/* Write the chart legend */
$myPicture->drawLegend(5,1,array(
  "Style"=>LEGEND_NOBORDER,
  "Mode"=>LEGEND_HORIZONTAL,
  "FontR"=>0,"FontG"=>0,"FontB"=>0,
  "FontName"=>PCHARTDIR."fonts/calibri.ttf",
  "FontSize"=>10
));

/* Render the picture */
$myPicture->stroke();