<?php

$years = $db->getRows("
    SELECT YEAR(`date`) AS `year`
    FROM `competitions`
    GROUP BY YEAR(`date`)
    ORDER BY `year`
");


$diss = array(array(
        'name' => 'HL',
        'dis' => 'HL',
        'sex' => 'male',
        'avgs' => array()
    ), array(
        'name' => 'HB männlich',
        'dis' => 'HB',
        'sex' => 'male',
        'avgs' => array()
    ), array(
        'name' => 'HB weiblich',
        'dis' => 'HB',
        'sex' => 'female',
        'avgs' => array()
    )
);

$labels  = array();


foreach ($years as $year) {
    $labels[] = substr($year['year'],2);

    foreach ($diss as $key => $dis) {
        $avgs = array();

        $competitions = $db->getRows("
          SELECT `c`.*,`p`.`name` AS `place`
          FROM `competitions` `c`
          INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
          WHERE YEAR(`c`.`date`) = '".$year['year']."'
          ORDER BY `c`.`date`;
        ");

        foreach ($competitions as $competition) {
            $count = $db->getRows("
                SELECT `person_id`
                FROM (
                    SELECT `s`.*, 1 AS `c`
                    FROM `scores` `s`
                    INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                    WHERE `s`.`competition_id` = '".$competition['id']."'
                    AND `s`.`discipline` = '".$db->escape($dis['dis'])."'
                    AND `p`.`sex` = '".$db->escape($dis['sex'])."'
                    AND `s`.`time` IS NOT NULL
                    ORDER BY `s`.`time`) `i`
                GROUP BY `i`.`person_id`
            ");
            if (count($count) < 25) {
                continue;
            }


            $avg = $db->getFirstRow("
                SELECT AVG(`time`) AS `avg`
                FROM (
                    SELECT `time`
                    FROM (
                      SELECT `time`
                      FROM (
                        SELECT `s`.*
                        FROM `scores` `s`
                        INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                        WHERE `s`.`competition_id` = '".$competition['id']."'
                        AND `s`.`discipline` = '".$db->escape($dis['dis'])."'
                        AND `p`.`sex` = '".$db->escape($dis['sex'])."'
                        AND `s`.`time` IS NOT NULL
                        ORDER BY `s`.`time`) `i`
                      GROUP BY `i`.`person_id`
                    ) `i2`
                    ORDER BY `i2`.`time`
                    LIMIT 5
                ) `i3`
            ", 'avg');

            if (is_numeric($avg)) $avgs[] = $avg;
        }

        if (count($avgs) === 0) {
            $diss[$key]['avgs'][] = VOID;
        } else {
            $sum = 0;
            foreach ($avgs as $avg) $sum += $avg;
            $diss[$key]['avgs'][] = c2s($sum/count($avgs));
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
$myPicture = Chart::create($w, $h, $MyData);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>Chart::size(8),"R"=>0,"G"=>0,"B"=>0));

/* Define the chart area */
$myPicture->setGraphArea(Chart::size(25),Chart::size(15),Chart::size(200),Chart::size(135));

/* Draw the scale */
$scaleSettings = array(
  "XMargin"=>0,
  "YMargin"=>0,
  "GridR"=>220,
  "GridG"=>220,
  "GridB"=>220,
  "Mode" => SCALE_MODE_MANUAL,
  "ManualScale" => array(array('Min'=>15, 'Max'=>21)),
  "CycleBackground"=>TRUE
);
$myPicture->drawScale($scaleSettings);

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>Chart::size(1),"Y"=>Chart::size(1),"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the line chart */
$myPicture->drawLineChart();

/* Write the chart legend */
$myPicture->drawLegend(Chart::size(5),Chart::size(1),array(
  "Style"=>LEGEND_NOBORDER,
  "Mode"=>LEGEND_HORIZONTAL,
  "FontR"=>0,"FontG"=>0,"FontB"=>0,
  "FontName"=>PCHARTDIR."fonts/calibri.ttf",
  "FontSize"=>Chart::size(10)
));

/* Render the picture */
$myPicture->stroke();
