<?php

// a == id
// b == name

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (Check::get('b')) $_GET['name'] = $_GET['b'];

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
} else {
    $competitions = $db->getRows("
        SELECT `id`
        FROM `competitions`
    ");
}

$counts = array( 0, 0, 0, 0, 0);
$labels = array(
    'Keine',
    '1 bis 10',
    '11 bis 20',
    '21 bis 30',
    '> 30',
);

foreach ($competitions as $competition) {

    $count = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM (
            SELECT `team`
            FROM (
                SELECT CONCAT(CAST(`team_id` AS CHAR),`sex`,CAST(`team_number` AS CHAR)) AS `team`
                FROM `scores_la`
                WHERE `competition_id` = '".$competition['id']."'
            UNION
                SELECT CONCAT(CAST(`team_id` AS CHAR),`sex`,CAST(`team_number` AS CHAR)) AS `team`
                FROM `scores_fs`
                WHERE `competition_id` = '".$competition['id']."'
            UNION
                SELECT CONCAT(CAST(`team_id` AS CHAR),'female',CAST(`team_number` AS CHAR)) AS `team`
                FROM `scores_gs`
                WHERE `competition_id` = '".$competition['id']."'
            UNION
                SELECT CONCAT(CAST(`team_id` AS CHAR),`pi`.`sex`,CAST(`team_number` AS CHAR)) AS `team`
                FROM `scores` `si`
                INNER JOIN `persons` `pi` ON `si`.`person_id` = `pi`.`id`
                WHERE `si`.`competition_id` = '".$competition['id']."'
            ) `i`
            GROUP BY `i`.`team`
        ) `i2`
    ", 'count');
    if ($count <= 0) $counts[0]++;
    elseif ($count < 11) $counts[1]++;
    elseif ($count < 21) $counts[2]++;
    elseif ($count < 31) $counts[3]++;
    else $counts[4]++;
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

