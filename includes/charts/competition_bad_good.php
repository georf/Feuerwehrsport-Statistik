<?php

// a == id
// b == key

if (Check::get('a')) $_GET['id'] = $_GET['a'];
if (Check::get('b')) $_GET['key'] = $_GET['b'];

if (!Check::get('id', 'key')) throw new Exception('not enough arguments');
if (!Check::isIn($_GET['id'], 'competitions')) throw new Exception('bad competition');
$id = intval($_GET['id']);

if ($_GET['key'] == 'full') {
    $key = 'full';
} else {

    $keys = explode('-', $_GET['key']);
    $key = $keys[0];

    $sex = false;
    $final = false;
    if (count($keys) > 1) {
        if (!empty($keys[1])) $sex = $keys[1];
        if (count($keys) > 2) {
            $final = true;
        }
    }

    if ($sex && !in_array($sex, array('male', 'female'))) throw new Exception('bad sex');

    $scores = array();
    $title  = '';
}

switch ($key) {
    case 'full':

        $good = $db->getFirstRow("
            SELECT COUNT(*) AS `good`
            FROM (
                SELECT `id`
                FROM `scores`
                WHERE `competition_id` = '".$db->escape($id)."'
                AND `time` IS NOT NULL
            UNION
                SELECT `id`
                FROM `scores_gs`
                WHERE `competition_id` = '".$db->escape($id)."'
                AND `time` IS NOT NULL
            UNION
                SELECT `id`
                FROM `scores_la`
                WHERE `competition_id` = '".$db->escape($id)."'
                AND `time` IS NOT NULL
            UNION
                SELECT `id`
                FROM `scores_fs`
                WHERE `competition_id` = '".$db->escape($id)."'
                AND `time` IS NOT NULL
            ) `i`
        ", 'good');

        $bad = $db->getFirstRow("
            SELECT COUNT(*) AS `bad`
            FROM (
                SELECT `id`
                FROM `scores`
                WHERE `competition_id` = '".$db->escape($id)."'
                AND `time` IS NULL
            UNION
                SELECT `id`
                FROM `scores_gs`
                WHERE `competition_id` = '".$db->escape($id)."'
                AND `time` IS NULL
            UNION
                SELECT `id`
                FROM `scores_la`
                WHERE `competition_id` = '".$db->escape($id)."'
                AND `time` IS NULL
            UNION
                SELECT `id`
                FROM `scores_fs`
                WHERE `competition_id` = '".$db->escape($id)."'
                AND `time` IS NULL
            ) `i`
        ", 'bad');
        $title = 'Ganzer Wettkampf';

        break;
    case 'gs':

        $good = $db->getFirstRow("
            SELECT COUNT(*) AS `good`
            FROM `scores_gs`
            WHERE `competition_id` = '".$db->escape($id)."'
            AND `time` IS NOT NULL
        ", 'good');
        $bad = $db->getFirstRow("
            SELECT COUNT(*) AS `bad`
            FROM `scores_gs`
            WHERE `competition_id` = '".$db->escape($id)."'
            AND `time` IS NULL
        ", 'bad');
        $title = FSS::dis2name($key);
        break;

    case 'la':
        if (!$sex) throw new Exception('sex not defined');

        $good = $db->getFirstRow("
            SELECT COUNT(*) AS `good`
            FROM `scores_la`
            WHERE `time` IS NOT NULL
            AND `sex` = '".$sex."'
            AND `competition_id` = '".$db->escape($id)."'
          AND `time` IS NOT NULL
        ", 'good');
        $bad = $db->getFirstRow("
            SELECT COUNT(*) AS `bad`
            FROM `scores_la`
            WHERE `time` IS NULL
            AND `sex` = '".$sex."'
            AND `competition_id` = '".$db->escape($id)."'
        ", 'bad');
        $title = FSS::dis2name($key).' '.FSS::sex($sex);
        break;

    case 'fs':
        if (!$sex) throw new Exception('sex not defined');

        $good = $db->getFirstRow("
            SELECT COUNT(*) AS `good`
            FROM `scores_fs`
            WHERE `time` IS NOT NULL
            AND `sex` = '".$sex."'
            AND `competition_id` = '".$db->escape($id)."'
          AND `time` IS NOT NULL
        ", 'good');
        $bad = $db->getFirstRow("
            SELECT COUNT(*) AS `bad`
            FROM `scores_fs`
            WHERE `time` IS NULL
            AND `sex` = '".$sex."'
            AND `competition_id` = '".$db->escape($id)."'
        ", 'bad');
        $title = FSS::dis2name($key).' '.FSS::sex($sex);
        break;

    case 'hb':
        if (!$sex) throw new Exception('sex not defined');

        if (!$final) {

            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores` `s`
                JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
                WHERE `time` IS NOT NULL
                AND `p`.`sex` = '".$sex."'
                AND `s`.`discipline` = 'HB'
                AND `s`.`team_number` != -2
                AND `s`.`competition_id` = '".$db->escape($id)."'
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores` `s`
                JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
                WHERE `time` IS NULL
                AND `p`.`sex` = '".$sex."'
                AND `s`.`discipline` = 'HB'
                AND `s`.`team_number` != -2
                AND `s`.`competition_id` = '".$db->escape($id)."'
            ", 'bad');
            $title = FSS::dis2name($key).' '.FSS::sex($sex);
        } else {

            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores` `s`
                JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
                WHERE `time` IS NOT NULL
                AND `p`.`sex` = '".$sex."'
                AND `s`.`discipline` = 'HB'
                AND `s`.`team_number` = -2
                AND `s`.`competition_id` = '".$db->escape($id)."'
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores` `s`
                JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
                WHERE `time` IS NULL
                AND `p`.`sex` = '".$sex."'
                AND `s`.`discipline` = 'HB'
                AND `s`.`team_number` = -2
                AND `s`.`competition_id` = '".$db->escape($id)."'
            ", 'bad');
            $title = FSS::dis2name($key).' '.FSS::sex($sex).' - Finale';
        }
        break;

    case 'hl':

        if (!$final) {

            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores`
                WHERE `time` IS NOT NULL
                AND `discipline` = 'HL'
                AND `team_number` != -2
                AND `competition_id` = '".$db->escape($id)."'
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores`
                WHERE `time` IS NULL
                AND `discipline` = 'HL'
                AND `team_number` != -2
                AND `competition_id` = '".$db->escape($id)."'
            ", 'bad');
            $title = FSS::dis2name($key);
        } else {

            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores`
                WHERE `time` IS NOT NULL
                AND `discipline` = 'HL'
                AND `team_number` = -2
                AND `competition_id` = '".$db->escape($id)."'
              AND `time` IS NOT NULL
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores`
                WHERE `time` IS NULL
                AND `discipline` = 'HL'
                AND `team_number` = -2
                AND `competition_id` = '".$db->escape($id)."'
            ", 'bad');
            $title = FSS::dis2name($key).' - Finale';
        }
        break;

    default:
        throw new Exception('bad key');
        break;
}

$MyData = new pData();
$MyData->addPoints(array($good, $bad), "time");
$MyData->addPoints(array('Gültig', 'Ungültig'), "Platzierung");
$MyData->setAbscissa("Platzierung");


$w = 140;
$h = 65;
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
$PieChart->draw2DPie(Chart::size(30),Chart::size(30), array(
    "WriteValues"=>PIE_VALUE_PERCENTAGE,
    "ValueR"=>50,
    "ValueG"=>50,
    "ValueB"=>50,
    "ValueAlpha"=>100,
    "Border"=>TRUE,
    "ValuePosition"=>PIE_VALUE_INSIDE,
    "SkewFactor"=>0.5,
    "Radius"=>Chart::size(30),
    "ValuePadding"=>Chart::size(15),
    "LabelStacked"=>true
));

$PieChart->drawPieLegend(Chart::size(68),Chart::size(17));

/* Render the picture */
$myPicture->stroke();

