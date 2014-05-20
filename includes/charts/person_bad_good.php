<?php

// a == id
// b == key

    if (Check::get('a')) $_GET['id'] = $_GET['a'];
    if (Check::get('b')) $_GET['key'] = $_GET['b'];

    if (!Check::get('id', 'key')) throw new Exception('not enough arguments');
    if (!Check::isIn($_GET['id'], 'persons')) throw new Exception('bad person');
    $id = intval($_GET['id']);
    $key = $_GET['key'];

    switch ($key) {

        case 'full':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM (
                    SELECT `s`.`id`
                    FROM `scores_gs` `s`
                    INNER JOIN `person_participations_gs` `p` ON `p`.`score_id` = `s`.`id`
                    WHERE `p`.`person_id` = '".$id."'
                    AND `time` IS NOT NULL
                UNION ALL
                    SELECT `s`.`id`
                    FROM `scores_la` `s`
                    INNER JOIN `person_participations_la` `p` ON `p`.`score_id` = `s`.`id`
                    WHERE `p`.`person_id` = '".$id."'
                    AND `time` IS NOT NULL
                UNION ALL
                    SELECT `s`.`id`
                    FROM `scores_fs` `s`
                    INNER JOIN `person_participations_fs` `p` ON `p`.`score_id` = `s`.`id`
                    WHERE `p`.`person_id` = '".$id."'
                    AND `time` IS NOT NULL
                UNION ALL
                    SELECT `id`
                    FROM `scores`
                    WHERE `time` IS NOT NULL
                    AND `person_id` = '".$id."'
                ) `i`
            ", 'good');

            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM (
                    SELECT `s`.`id`
                    FROM `scores_gs` `s`
                    INNER JOIN `person_participations_gs` `p` ON `p`.`score_id` = `s`.`id`
                    WHERE `p`.`person_id` = '".$id."'
                    AND `time` IS NULL
                UNION ALL
                    SELECT `s`.`id`
                    FROM `scores_la` `s`
                    INNER JOIN `person_participations_la` `p` ON `p`.`score_id` = `s`.`id`
                    WHERE `p`.`person_id` = '".$id."'
                    AND `time` IS NULL
                UNION ALL
                    SELECT `s`.`id`
                    FROM `scores_fs` `s`
                    INNER JOIN `person_participations_fs` `p` ON `p`.`score_id` = `s`.`id`
                    WHERE `p`.`person_id` = '".$id."'
                    AND `time` IS NULL
                UNION ALL
                    SELECT `id`
                    FROM `scores`
                    WHERE `time` IS NULL
                    AND `person_id` = '".$id."'
                ) `i`
            ", 'bad');
            $title = 'Ganzer Wettkampf';

            break;

        case 'gs':
        case 'la':
        case 'fs':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores_".$key."` `s`
                INNER JOIN `person_participations_".$key."` `p` ON `p`.`score_id` = `s`.`id`
                WHERE `p`.`person_id` = '".$id."'
                AND `time` IS NOT NULL
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores_".$key."` `s`
                INNER JOIN `person_participations_".$key."` `p` ON `p`.`score_id` = `s`.`id`
                WHERE `p`.`person_id` = '".$id."'
                AND `time` IS NULL
            ", 'bad');
            $title = FSS::dis2name($key);
            break;

        case 'hb':
        case 'hl':
            $good = $db->getFirstRow("
                SELECT COUNT(*) AS `good`
                FROM `scores`
                WHERE `time` IS NOT NULL
                AND `person_id` = '".$id."'
                AND `discipline` = '".$key."'
            ", 'good');
            $bad = $db->getFirstRow("
                SELECT COUNT(*) AS `bad`
                FROM `scores`
                WHERE `time` IS NULL
                AND `person_id` = '".$id."'
                AND `discipline` = '".$key."'
            ", 'bad');
            $title = FSS::dis2name($key);
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
