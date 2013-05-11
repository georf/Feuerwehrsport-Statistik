<?php

try {
    require_once(__DIR__.'/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

if (Check::post('competition_id') && isset($_GET['competition']) && Check::isIn($_GET['competition'], 'competitions')) {
    $id = intval($_GET['competition']);

    $objPHPExcel = Cache::get();
    if (!$objPHPExcel) {

        $competition = $db->getFirstRow("
            SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`,
                `t`.`persons`,`t`.`run`,`t`.`score`,`t`.`id` AS `score_type`
            FROM `competitions` `c`
            INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
            INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
            LEFT JOIN `score_types` `t` ON `t`.`id` = `c`.`score_type_id`
            WHERE `c`.`id` = '".$id."'
            LIMIT 1;");

        $worksheetCount = 0;
        $zweikampf = array();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Feuerwehrsport - Statistik")
                                     ->setTitle($competition['event'].' - '.$competition['place'].' - '.gdate($competition['date']))
                                     ->setSubject("Statistik")
                                     ->setDescription("Stand vom ".date('d.m.Y'))
                                     ->setKeywords("Statistik, ".$competition['event'].', '.$competition['place'].', '.gdate($competition['date']));



        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setTitle('Übersicht');
        $overview = array();

        $worksheetCount++;



        $simple = array(
            'hbw' => array(

                // HB - weiblich
                'where' => " `competition_id` = '".$id."' AND `discipline` = 'HB' AND `p`.`sex` = 'female' ",
                'name' => 'Hindernisbahn weiblich',
                'short' => 'HB weiblich'
            ),
            'hbm' => array(

                // HB - männlich
                'where' => " `competition_id` = '".$id."' AND `discipline` = 'HB' AND `p`.`sex` = 'male' ",
                'name' => 'Hindernisbahn männlich',
                'short' => 'HB männlich'
            ),
            'hl' => array(

                // HB - männlich
                'where' => " `competition_id` = '".$id."' AND `discipline` = 'HL' AND `p`.`sex` = 'male' ",
                'name' => 'Hakenleitersteigen',
                'short' => 'HL'
            ),
        );


        foreach ($simple as $disKey => $disValue) {

            $scores = $db->getRows("
                        SELECT *
                        FROM (
                            SELECT *
                            FROM (
                                (
                                  SELECT `s`.`time`,`p`.*,`s`.`team_id`, `t`.`name` AS `team`,`s`.`id` AS `score_id`,`s`.`team_number`
                                  FROM `scores` `s`
                                  INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                                  LEFT JOIN `teams` `t` ON `t`.`id` = `s`.`team_id`
                                  WHERE ".$disValue['where']."
                                  AND `s`.`time` IS NOT NULL
                                  AND `team_number` != -2
                                  ORDER BY `s`.`time`
                                )
                                UNION
                                (
                                  SELECT 99999999 AS `time`,`p`.*,`s`.`team_id`, `t`.`name` AS `team`,`s`.`id` AS `score_id`,`s`.`team_number`
                                  FROM `scores` `s`
                                  INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                                  LEFT JOIN `teams` `t` ON `t`.`id` = `s`.`team_id`
                                  WHERE ".$disValue['where']."
                                  AND `s`.`time` IS NULL
                                  AND `team_number` != -2
                                )
                                ORDER BY `time`
                            ) `i`
                            GROUP BY `i`.`id`
                        ) `i2`
                        ORDER BY `i2`.`time`
                    ");

            if (count($scores)) {
                $overview[] = array($disValue['name'], count($scores));

                $worksheet = $objPHPExcel->createSheet($worksheetCount);

                $worksheet->setTitle($disValue['short']);
                $worksheet->mergeCells('A1:E1');
                $worksheet->setCellValue('A1', $disValue['name']);
                setExcelBold($worksheet, 'A1');
                setExcelHCenter($worksheet, 'A1');

                // Build headline
                setExcelHead($worksheet, 'A3', 'Platz');
                setExcelHead($worksheet, 'B3', 'Name');
                setExcelHead($worksheet, 'C3', 'Vorame');
                setExcelHead($worksheet, 'D3', 'Mannschaft');
                setExcelHead($worksheet, 'E3', 'Zeit');

                $moreScoresCount = 0;

                $teams = array();
                for($line = 0; $line < count($scores); $line++) {
                    $score = $scores[$line];

                    if ($competition['score_type']) {
                        if ($score['team_number'] < 0) {
                            $mannschaft = 'E';
                        } else {
                            $mannschaft = $score['team_number']+1;
                        }

                        if ($score['team'] && $mannschaft != 'E') {
                            $mannschaftId = $score['team'].$mannschaft;

                            if (!isset($teams[$mannschaftId])) {
                                $teams[$mannschaftId] = array(
                                    'name' => $score['team'].' '.$mannschaft,
                                    'persons' => array()
                                );
                            }
                            $teams[$mannschaftId]['persons'][] = $score;
                        }
                    }

                    $place = $line;
                    while (isset($scores[$place - 1]) && $scores[$place - 1]['time'] == $score['time']) {
                        $place = $place - 1;
                    }

                    if (!$competition['score_type']) {
                        $mannschaft = $score['team'];
                    } else {

                        if (empty($score['team'])) {
                            $mannschaft = '';
                        } else {
                            if ($mannschaft == '1') {
                                $mannschaft = $score['team'];
                            } else {
                                $mannschaft = $score['team'].' '.$mannschaft;
                            }
                        }
                    }

                    if ($score['time'] != 99999999 && ($disKey == 'hl' || $disKey == 'hbm')) {
                        if (!isset($zweikampf[$score['id']])) $zweikampf[$score['id']] = array();
                        $zweikampf[$score['id']][$disKey] = $score;
                    }

                    $tr = $line + 4;
                    $worksheet->setCellValueExplicit('A'.$tr, strval(($place+1).'.'));
                    setExcelHCenter($worksheet, 'A'.$tr);
                    setExcelBorder($worksheet, 'A'.$tr);

                    $worksheet->setCellValue('B'.$tr, $score['name']);
                    setExcelBorder($worksheet, 'B'.$tr);

                    $worksheet->setCellValue('C'.$tr, $score['firstname']);
                    setExcelBorder($worksheet, 'C'.$tr);

                    $worksheet->setCellValue('D'.$tr, $mannschaft);
                    setExcelBorder($worksheet, 'D'.$tr);

                    setExcelTime($worksheet, 'E'.$tr, $score['time']);
                    setExcelBorder($worksheet, 'E'.$tr);

                    // search for more times
                    $moreScores = $db->getRows("
                        (
                            SELECT `s`.`time`,`s`.`team_id`, `t`.`name` AS `team`,`s`.`id` AS `score_id`,`s`.`team_number`
                            FROM `scores` `s`
                            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                            LEFT JOIN `teams` `t` ON `t`.`id` = `s`.`team_id`
                            WHERE ".$disValue['where']."
                            AND `person_id` = '".$score['id']."'
                            AND `s`.`id` != '".$score['score_id']."'
                            AND `s`.`time` IS NOT NULL
                            AND `team_number` != -2
                            ORDER BY `s`.`time`
                        )
                        UNION
                        (
                            SELECT 99999999 AS `time`,`s`.`team_id`, `t`.`name` AS `team`,`s`.`id` AS `score_id`,`s`.`team_number`
                            FROM `scores` `s`
                            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                            LEFT JOIN `teams` `t` ON `t`.`id` = `s`.`team_id`
                            WHERE ".$disValue['where']."
                            AND `person_id` = '".$score['id']."'
                            AND `s`.`id` != '".$score['score_id']."'
                            AND `s`.`time` IS NULL
                            AND `team_number` != -2
                        )
                        ORDER BY `time`
                    ");

                    $moreScoresCount = max(count($moreScores), $moreScoresCount);

                    $chr = 70;
                    foreach($moreScores as $moreScore) {
                        setExcelTime($worksheet, chr($chr).$tr, $moreScore['time']);
                        setExcelBorder($worksheet, chr($chr).$tr);
                        $chr++;
                    }

                }

                $chr = 70;
                for ($i = 0; $i < $moreScoresCount; $i++) {
                    setExcelHead($worksheet, chr($chr+$i).'3', 'Zeit '.($i+2));
                    $calculatedWidth = $worksheet->getColumnDimension(chr($chr+$i))->getWidth();
                    $worksheet->getColumnDimension(chr($chr+$i))->setWidth((int) $calculatedWidth * 1.05);
                }


                $calculatedWidth = $worksheet->getColumnDimension('E')->getWidth();
                $worksheet->getColumnDimension('E')->setWidth((int) $calculatedWidth * 1.05);

                foreach (array('A','B','C','D') as $letter) {
                    $worksheet->getColumnDimension($letter)->setAutoSize(true);
                }

                $worksheetCount++;


                if ($competition['score_type'] && count($teams)) {
                    $worksheet = $objPHPExcel->createSheet($worksheetCount);

                    $worksheet->setTitle('Mannschaft '.$disValue['short']);
                    $worksheet->mergeCells('A1:E1');
                    $worksheet->setCellValue('A1', 'Mannschaft '.$disValue['name']);

                    setExcelBold($worksheet, 'A1');
                    setExcelHCenter($worksheet, 'A1');

                    // sort every persons in teams
                    foreach ($teams as $key => $team) {
                        $time = 0;

                        usort($team['persons'], function($a, $b) {
                            if ($a['time'] == $b['time']) return 0;
                            elseif ($a['time'] > $b['time']) return 1;
                            else return -1;
                        });

                        if (count($team['persons']) < $competition['score']) {

                            $teams[$key]['time'] = PHP_INT_MAX;
                            continue;
                        }

                        for($i = 0; $i < $competition['score']; $i++) {
                            if ($team['persons'][$i]['time'] == 99999999) {
                                $teams[$key]['time'] = PHP_INT_MAX;
                                continue 2;
                            }
                            $time += $team['persons'][$i]['time'];
                        }
                        $teams[$key]['time'] = $time;
                    }

                    uasort($teams, function ($a, $b) {
                        if ($a['time'] == $b['time']) return 0;
                        elseif ($a['time'] > $b['time']) return 1;
                        else return -1;
                    });


                    $tr = 3;
                    $place = 0;
                    $lastTime = -1;
                    $lastPlace = 0;
                    foreach ($teams as $key => $team) {

                        $place++;
                        $curPlace = $place;
                        if ($lastTime == $team['time']) {
                            $curPlace = $lastPlace;
                        }
                        $lastPlace = $curPlace;

                        // Calculatet place
                        $worksheet->setCellValueExplicit('A'.$tr, strval($curPlace.'.'));
                        $worksheet->mergeCells('A'.$tr.':A'.($tr+count($team['persons'])));

                        // Calculatet time
                        setExcelTime($worksheet, 'B'.$tr, $team['time']);
                        $worksheet->mergeCells('B'.$tr.':B'.($tr+count($team['persons'])));

                        setExcelBorder($worksheet, 'A'.$tr.':B'.($tr+count($team['persons'])));
                        setExcelHCenter($worksheet, 'A'.$tr.':B'.$tr);
                        setExcelVCenter($worksheet, 'A'.$tr.':B'.$tr);


                        // Team
                        $worksheet->setCellValue('C'.$tr, $team['name']);
                        $worksheet->mergeCells('C'.$tr.':E'.$tr);
                        setExcelBorder($worksheet, 'C'.$tr.':E'.$tr);
                        setExcelBold($worksheet, 'C'.$tr.':E'.$tr);
                        setExcelHCenter($worksheet, 'C'.$tr.':E'.$tr);



                        for($i = 0; $i < count($team['persons']); $i++) {
                            $tr++;
                            setExcelTime($worksheet, 'C'.$tr, $team['persons'][$i]['time']);
                            $worksheet->setCellValue('D'.$tr, $team['persons'][$i]['name'])
                                      ->setCellValue('E'.$tr, $team['persons'][$i]['firstname']);


                            setExcelBorder($worksheet, 'C'.$tr.':E'.$tr);

                            if ($i < $competition['score']) {
                                setExcelBackground($worksheet, 'C'.$tr.':E'.$tr, 'FFE5E5E5');
                            } elseif ($i > $competition['run']) {
                                setExcelBackground($worksheet, 'C'.$tr.':E'.$tr, 'FFFFB2B2');
                            }
                        }
                        $tr++;
                    }

                    foreach (array('B','C') as $letter) {
                        $calculatedWidth = $worksheet->getColumnDimension($letter)->getWidth();
                        $worksheet->getColumnDimension($letter)->setWidth((int) $calculatedWidth * 1.05);
                    }
                    foreach (array('A','D','E') as $letter) {
                        $worksheet->getColumnDimension($letter)->setAutoSize(true);
                    }

                    $worksheetCount++;
                }
            }




            $finals = $db->getRows("
                        SELECT *
                        FROM (
                            SELECT *
                            FROM (
                                (
                                  SELECT `s`.`time`,`p`.*,`s`.`team_id`, `t`.`name` AS `team`,`s`.`id` AS `score_id`,`s`.`team_number`
                                  FROM `scores` `s`
                                  INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                                  LEFT JOIN `teams` `t` ON `t`.`id` = `s`.`team_id`
                                  WHERE ".$disValue['where']."
                                  AND `s`.`time` IS NOT NULL
                                  AND `team_number` = -2
                                  ORDER BY `s`.`time`
                                )
                                UNION
                                (
                                  SELECT 99999999 AS `time`,`p`.*,`s`.`team_id`, `t`.`name` AS `team`,`s`.`id` AS `score_id`,`s`.`team_number`
                                  FROM `scores` `s`
                                  INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                                  LEFT JOIN `teams` `t` ON `t`.`id` = `s`.`team_id`
                                  WHERE ".$disValue['where']."
                                  AND `s`.`time` IS NULL
                                  AND `team_number` = -2
                                )
                                ORDER BY `time`
                            ) `i`
                            GROUP BY `i`.`id`
                        ) `i2`
                        ORDER BY `i2`.`time`
                    ");

            if (count($finals)) {
                $overview[] = array($disValue['name'].' Finale', count($finals));

                $worksheet = $objPHPExcel->createSheet($worksheetCount);

                $worksheet->setTitle($disValue['short'].' F');
                $worksheet->mergeCells('A1:E1');
                $worksheet->setCellValue('A1', $disValue['name'].' Finale');
                setExcelBold($worksheet, 'A1');
                setExcelHCenter($worksheet, 'A1');

                // Build headline
                setExcelHead($worksheet, 'A3', 'Platz');
                setExcelHead($worksheet, 'B3', 'Name');
                setExcelHead($worksheet, 'C3', 'Vorame');
                setExcelHead($worksheet, 'D3', 'Mannschaft');
                setExcelHead($worksheet, 'E3', 'Zeit');

                $moreScoresCount = 0;

                $teams = array();
                for($line = 0; $line < count($finals); $line++) {
                    $score = $finals[$line];

                    if ($competition['score_type']) {
                        if ($score['team_number'] < 0) {
                            $mannschaft = 'E';
                        } else {
                            $mannschaft = $score['team_number']+1;
                        }
                    }

                    $place = $line;
                    while (isset($finals[$place - 1]) && $finals[$place - 1]['time'] == $score['time']) {
                        $place = $place - 1;
                    }

                    if (!$competition['score_type']) {
                        $mannschaft = $score['team'];
                    } else {

                        if (empty($score['team'])) {
                            $mannschaft = '';
                        } else {
                            if ($mannschaft == '1') {
                                $mannschaft = $score['team'];
                            } else {
                                $mannschaft = $score['team'].' '.$mannschaft;
                            }
                        }
                    }

                    $tr = $line + 4;
                    $worksheet->setCellValueExplicit('A'.$tr, strval(($place+1).'.'));
                    setExcelHCenter($worksheet, 'A'.$tr);
                    setExcelBorder($worksheet, 'A'.$tr);

                    $worksheet->setCellValue('B'.$tr, $score['name']);
                    setExcelBorder($worksheet, 'B'.$tr);

                    $worksheet->setCellValue('C'.$tr, $score['firstname']);
                    setExcelBorder($worksheet, 'C'.$tr);

                    $worksheet->setCellValue('D'.$tr, $mannschaft);
                    setExcelBorder($worksheet, 'D'.$tr);

                    setExcelTime($worksheet, 'E'.$tr, $score['time']);
                    setExcelBorder($worksheet, 'E'.$tr);

                    // search for more times
                    $moreScores = $db->getRows("
                        (
                            SELECT `s`.`time`,`s`.`team_id`, `t`.`name` AS `team`,`s`.`id` AS `score_id`,`s`.`team_number`
                            FROM `scores` `s`
                            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                            LEFT JOIN `teams` `t` ON `t`.`id` = `s`.`team_id`
                            WHERE ".$disValue['where']."
                            AND `person_id` = '".$score['id']."'
                            AND `s`.`id` != '".$score['score_id']."'
                            AND `s`.`time` IS NOT NULL
                            AND `team_number` = -2
                            ORDER BY `s`.`time`
                        )
                        UNION
                        (
                            SELECT 99999999 AS `time`,`s`.`team_id`, `t`.`name` AS `team`,`s`.`id` AS `score_id`,`s`.`team_number`
                            FROM `scores` `s`
                            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                            LEFT JOIN `teams` `t` ON `t`.`id` = `s`.`team_id`
                            WHERE ".$disValue['where']."
                            AND `person_id` = '".$score['id']."'
                            AND `s`.`id` != '".$score['score_id']."'
                            AND `s`.`time` IS NULL
                            AND `team_number` = -2
                        )
                        ORDER BY `time`
                    ");

                    $moreScoresCount = max(count($moreScores), $moreScoresCount);

                    $chr = 70;
                    foreach($moreScores as $moreScore) {
                        setExcelTime($worksheet, chr($chr).$tr, $moreScore['time']);
                        setExcelBorder($worksheet, chr($chr).$tr);
                        $chr++;
                    }

                }

                $chr = 70;
                for ($i = 0; $i < $moreScoresCount; $i++) {
                    setExcelHead($worksheet, chr($chr+$i).'3', 'Zeit '.($i+2));
                    $calculatedWidth = $worksheet->getColumnDimension(chr($chr+$i))->getWidth();
                    $worksheet->getColumnDimension(chr($chr+$i))->setWidth((int) $calculatedWidth * 1.05);
                }


                $calculatedWidth = $worksheet->getColumnDimension('E')->getWidth();
                $worksheet->getColumnDimension('E')->setWidth((int) $calculatedWidth * 1.05);

                foreach (array('A','B','C','D') as $letter) {
                    $worksheet->getColumnDimension($letter)->setAutoSize(true);
                }

                $worksheetCount++;
            }

        }


        $scores = array();
        foreach ($zweikampf as $score) {
            if (!isset($score['hl'],$score['hbm'])) continue;
            $score['firstname'] = $score['hl']['firstname'];
            $score['name'] = $score['hl']['name'];
            $score['hl'] = $score['hl']['time'];
            $score['hbm'] = $score['hbm']['time'];
            $score['time'] = $score['hl'] + $score['hbm'];
            $scores[] = $score;
        }

        if (count($scores)) {
            $name = 'Zweikampf';
            $overview[] = array($name, count($scores));

            usort($scores, function ($a, $b) {
                if ($a['time'] == $b['time']) return 0;
                elseif ($a['time'] > $b['time']) return 1;
                else return -1;
            });

            $worksheet = $objPHPExcel->createSheet($worksheetCount);

            $worksheet->setTitle($name);
            $worksheet->mergeCells('A1:F1');
            $worksheet->setCellValue('A1', $name);
            setExcelBold($worksheet, 'A1');
            setExcelHCenter($worksheet, 'A1');

            // Build headline
            setExcelHead($worksheet, 'A3', 'Platz');
            setExcelHead($worksheet, 'B3', 'Name');
            setExcelHead($worksheet, 'C3', 'Vorame');
            setExcelHead($worksheet, 'D3', 'HB');
            setExcelHead($worksheet, 'E3', 'HL');
            setExcelHead($worksheet, 'F3', 'Zeit');


            for($line = 0; $line < count($scores); $line++) {
                $score = $scores[$line];


                $place = $line;
                while (isset($scores[$place - 1]) && $scores[$place - 1]['time'] == $score['time']) {
                    $place = $place - 1;
                }


                $tr = $line + 4;
                $worksheet->setCellValueExplicit('A'.$tr, strval(($place+1).'.'));
                setExcelHCenter($worksheet, 'A'.$tr);
                setExcelBorder($worksheet, 'A'.$tr);

                $worksheet->setCellValue('B'.$tr, $score['name']);
                setExcelBorder($worksheet, 'B'.$tr);

                $worksheet->setCellValue('C'.$tr, $score['firstname']);
                setExcelBorder($worksheet, 'C'.$tr);

                setExcelTime($worksheet, 'D'.$tr, $score['hbm']);
                setExcelBorder($worksheet, 'D'.$tr);

                setExcelTime($worksheet, 'E'.$tr, $score['hl']);
                setExcelBorder($worksheet, 'E'.$tr);

                setExcelTime($worksheet, 'F'.$tr, $score['time']);
                setExcelBorder($worksheet, 'F'.$tr);
            }


            foreach (array('D','E','F') as $letter) {
                $calculatedWidth = $worksheet->getColumnDimension($letter)->getWidth();
                $worksheet->getColumnDimension($letter)->setWidth((int) $calculatedWidth * 1.05);
            }

            foreach (array('A','B','C') as $letter) {
                $worksheet->getColumnDimension($letter)->setAutoSize(true);
            }

            $worksheetCount++;
        }


        $simple = array(
            'female' => 'weiblich',
            'male' => 'männlich',
        );

        foreach ($simple as $sex => $name) {
            $scores = $db->getRows("
                SELECT `g`.*,
                    `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
                    `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
                    `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
                    `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
                    `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`,
                    `p5`.`name` AS `name5`,`p5`.`firstname` AS `firstname5`,
                    `p6`.`name` AS `name6`,`p6`.`firstname` AS `firstname6`,
                    `p7`.`name` AS `name7`,`p7`.`firstname` AS `firstname7`
                FROM (
                    SELECT *
                    FROM (
                        (
                            SELECT `id`,`team_id`,`team_number`,
                            `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`,
                            `time`
                            FROM `scores_la` `gC`
                            WHERE `time` IS NOT NULL
                            AND `gC`.`sex` = '".$sex."'
                            AND `gC`.`competition_id` = '".$id."'
                        ) UNION (
                            SELECT `id`,`team_id`,`team_number`,
                            `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`,
                            99999999 AS `time`
                            FROM `scores_la` `gD`
                            WHERE `time` IS NULL
                            AND `gD`.`sex` = '".$sex."'
                            AND `gD`.`competition_id` = '".$id."'
                        ) ORDER BY `time`
                    ) `gi`
                    GROUP BY `team_id`,`team_number`
                ) `g`

                INNER JOIN `teams` `t` ON `t`.`id` = `g`.`team_id`
                LEFT JOIN `persons` `p1` ON `g`.`person_1` = `p1`.`id`
                LEFT JOIN `persons` `p2` ON `g`.`person_2` = `p2`.`id`
                LEFT JOIN `persons` `p3` ON `g`.`person_3` = `p3`.`id`
                LEFT JOIN `persons` `p4` ON `g`.`person_4` = `p4`.`id`
                LEFT JOIN `persons` `p5` ON `g`.`person_5` = `p5`.`id`
                LEFT JOIN `persons` `p6` ON `g`.`person_6` = `p6`.`id`
                LEFT JOIN `persons` `p7` ON `g`.`person_7` = `p7`.`id`
                ORDER BY `time`
            ");

            if (count($scores)) {
                $overview[] = array('Löschangriff '.$name, count($scores));

                $wks = 7;

                $worksheet = $objPHPExcel->createSheet($worksheetCount);

                $worksheet->setTitle('LA '.$name);
                $worksheet->mergeCells('A1:F1');
                $worksheet->setCellValue('A1', 'Löschangriff '.$name);
                setExcelBold($worksheet, 'A1');
                setExcelHCenter($worksheet, 'A1');

                setExcelHead($worksheet, 'A3', 'Platz');
                setExcelHead($worksheet, 'B3', 'Mannschaft');
                setExcelHead($worksheet, 'C3', 'Zeit');

                $moreScoresCount = 0;

                for($line = 0; $line < count($scores); $line++) {
                    $score = $scores[$line];
                    $tr = $line + 4;
                    $scores[$line]['tr'] = $tr;

                    $team_number = '';
                    if ($score['team_number']) $team_number =  ' '.(intval($score['team_number'])+1);


                    $place = $line;
                    while (isset($scores[$place - 1]) && $scores[$place - 1]['time'] == $score['time']) {
                        $place = $place - 1;
                    }


                    $worksheet->setCellValueExplicit('A'.$tr, strval(($place+1).'.'));
                    setExcelHCenter($worksheet, 'A'.$tr);
                    setExcelBorder($worksheet, 'A'.$tr);

                    $worksheet->setCellValue('B'.$tr, $score['team'].$team_number);
                    setExcelBorder($worksheet, 'B'.$tr);

                    setExcelTime($worksheet, 'C'.$tr, $score['time']);
                    setExcelBorder($worksheet, 'C'.$tr);

                    $moreScores = $db->getRows("
                        (
                            SELECT `time`
                            FROM `scores_la` `gC`
                            WHERE `time` IS NOT NULL
                            AND `gC`.`sex` = '".$sex."'
                            AND `gC`.`competition_id` = '".$id."'
                            AND `id` != '".$score['id']."'
                            AND `team_id` = '".$score['team_id']."'
                            AND `team_number` = '".$score['team_number']."'
                        ) UNION (
                            SELECT 99999999 AS `time`
                            FROM `scores_la` `gD`
                            WHERE `time` IS NULL
                            AND `gD`.`sex` = '".$sex."'
                            AND `gD`.`competition_id` = '".$id."'
                            AND `id` != '".$score['id']."'
                            AND `team_id` = '".$score['team_id']."'
                            AND `team_number` = '".$score['team_number']."'
                        ) ORDER BY `time`
                    ");

                    $moreScoresCount = max(count($moreScores), $moreScoresCount);

                    $chr = 68;
                    foreach($moreScores as $moreScore) {
                        setExcelTime($worksheet, chr($chr).$tr, $moreScore['time']);
                        setExcelBorder($worksheet, chr($chr).$tr);
                        $chr++;
                    }
                }

                $chr = 68 + $moreScoresCount;
                foreach ($scores as $score) {
                    for ($i = 0; $i < $wks; $i++) {
                        $worksheet->setCellValue(chr($chr + $i).$score['tr'], $score['firstname'.($i+1)].' '.$score['name'.($i+1)]);
                        setExcelBorder($worksheet, chr(68 + $moreScoresCount + $i).$score['tr']);
                    }
                }

                for ($i = 0; $i < $wks; $i++) {
                    setExcelHead($worksheet, chr($chr + $i).'3', 'WK'.($i+1));
                    $worksheet->getColumnDimension(chr($chr + $i))->setAutoSize(true);
                }

                for ($i = 0; $i < $moreScoresCount + 1; $i++) {
                    if ($i > 0) setExcelHead($worksheet, chr(67 + $i).'3', 'Zeit '.($i+1));

                    $calculatedWidth = $worksheet->getColumnDimension(chr(67 + $i))->getWidth();
                    $worksheet->getColumnDimension(chr(67 + $i))->setWidth((int) $calculatedWidth * 1.05);
                }

                foreach (array('A','B') as $letter) {
                    $worksheet->getColumnDimension($letter)->setAutoSize(true);
                }

                $worksheetCount++;
            }
        }


        $scores = $db->getRows("
            SELECT `g`.*,
                `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
                `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
                `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
                `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
                `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`,
                `p5`.`name` AS `name5`,`p5`.`firstname` AS `firstname5`,
                `p6`.`name` AS `name6`,`p6`.`firstname` AS `firstname6`
            FROM (
                SELECT *
                FROM (
                    (
                        SELECT `id`,`team_id`,`team_number`,
                        `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,
                        `time`
                        FROM `scores_gs` `gC`
                        WHERE `time` IS NOT NULL
                        AND `gC`.`competition_id` = '".$id."'
                    ) UNION (
                        SELECT `id`,`team_id`,`team_number`,
                        `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,
                        99999999 AS `time`
                        FROM `scores_gs` `gD`
                        WHERE `time` IS NULL
                        AND `gD`.`competition_id` = '".$id."'
                    ) ORDER BY `time`
                ) `gi`
                GROUP BY `team_id`,`team_number`
            ) `g`

            INNER JOIN `teams` `t` ON `t`.`id` = `g`.`team_id`
            LEFT JOIN `persons` `p1` ON `g`.`person_1` = `p1`.`id`
            LEFT JOIN `persons` `p2` ON `g`.`person_2` = `p2`.`id`
            LEFT JOIN `persons` `p3` ON `g`.`person_3` = `p3`.`id`
            LEFT JOIN `persons` `p4` ON `g`.`person_4` = `p4`.`id`
            LEFT JOIN `persons` `p5` ON `g`.`person_5` = `p5`.`id`
            LEFT JOIN `persons` `p6` ON `g`.`person_6` = `p6`.`id`
            ORDER BY `time`
        ");

        if (count($scores)) {
            $overview[] = array('Gruppenstafette', count($scores));

            $wks = 6;

            $worksheet = $objPHPExcel->createSheet($worksheetCount);

            $worksheet->setTitle('GS');
            $worksheet->mergeCells('A1:F1');
            $worksheet->setCellValue('A1', 'Gruppenstafette');
            setExcelBold($worksheet, 'A1');
            setExcelHCenter($worksheet, 'A1');

            setExcelHead($worksheet, 'A3', 'Platz');
            setExcelHead($worksheet, 'B3', 'Mannschaft');
            setExcelHead($worksheet, 'C3', 'Zeit');

            $moreScoresCount = 0;

            for($line = 0; $line < count($scores); $line++) {
                $score = $scores[$line];
                $tr = $line + 4;
                $scores[$line]['tr'] = $tr;

                $team_number = '';
                if ($score['team_number']) $team_number =  ' '.(intval($score['team_number'])+1);


                $place = $line;
                while (isset($scores[$place - 1]) && $scores[$place - 1]['time'] == $score['time']) {
                    $place = $place - 1;
                }


                $worksheet->setCellValueExplicit('A'.$tr, strval(($place+1).'.'));
                setExcelHCenter($worksheet, 'A'.$tr);
                setExcelBorder($worksheet, 'A'.$tr);

                $worksheet->setCellValue('B'.$tr, $score['team'].$team_number);
                setExcelBorder($worksheet, 'B'.$tr);

                setExcelTime($worksheet, 'C'.$tr, $score['time']);
                setExcelBorder($worksheet, 'C'.$tr);

                $moreScores = $db->getRows("
                    (
                        SELECT `time`
                        FROM `scores_gs` `gC`
                        WHERE `time` IS NOT NULL
                        AND `gC`.`competition_id` = '".$id."'
                        AND `id` != '".$score['id']."'
                        AND `team_id` = '".$score['team_id']."'
                        AND `team_number` = '".$score['team_number']."'
                    ) UNION (
                        SELECT 99999999 AS `time`
                        FROM `scores_gs` `gD`
                        WHERE `time` IS NULL
                        AND `gD`.`competition_id` = '".$id."'
                        AND `id` != '".$score['id']."'
                        AND `team_id` = '".$score['team_id']."'
                        AND `team_number` = '".$score['team_number']."'
                    ) ORDER BY `time`
                ");

                $moreScoresCount = max(count($moreScores), $moreScoresCount);

                $chr = 68;
                foreach($moreScores as $moreScore) {
                    setExcelTime($worksheet, chr($chr).$tr, $moreScore['time']);
                    setExcelBorder($worksheet, chr($chr).$tr);
                    $chr++;
                }
            }

            $chr = 68 + $moreScoresCount;
            foreach ($scores as $score) {
                for ($i = 0; $i < $wks; $i++) {
                    $worksheet->setCellValue(chr($chr + $i).$score['tr'], $score['firstname'.($i+1)].' '.$score['name'.($i+1)]);
                    setExcelBorder($worksheet, chr(68 + $moreScoresCount + $i).$score['tr']);
                }
            }

            for ($i = 0; $i < $wks; $i++) {
                setExcelHead($worksheet, chr($chr + $i).'3', 'WK'.($i+1));
                $worksheet->getColumnDimension(chr($chr + $i))->setAutoSize(true);
            }

            for ($i = 0; $i < $moreScoresCount + 1; $i++) {
                if ($i > 0) setExcelHead($worksheet, chr(67 + $i).'3', 'Zeit '.($i+1));

                $calculatedWidth = $worksheet->getColumnDimension(chr(67 + $i))->getWidth();
                $worksheet->getColumnDimension(chr(67 + $i))->setWidth((int) $calculatedWidth * 1.05);
            }

            foreach (array('A','B') as $letter) {
                $worksheet->getColumnDimension($letter)->setAutoSize(true);
            }

            $worksheetCount++;
        }







        $simple = array(
            'female' => 'weiblich',
            'male' => 'männlich',
        );

        foreach ($simple as $sex => $name) {
            $scores = $db->getRows("
                SELECT `g`.*,
                    `t`.`name` AS `team`,`t`.`short` AS `shortteam`,`run`,
                    `p1`.`name` AS `name1`,`p1`.`firstname` AS `firstname1`,
                    `p2`.`name` AS `name2`,`p2`.`firstname` AS `firstname2`,
                    `p3`.`name` AS `name3`,`p3`.`firstname` AS `firstname3`,
                    `p4`.`name` AS `name4`,`p4`.`firstname` AS `firstname4`
                FROM (
                    SELECT *
                    FROM (
                        (
                            SELECT `id`,`team_id`,`team_number`,
                            `person_1`,`person_2`,`person_3`,`person_4`,
                            `time`,`run`
                            FROM `scores_fs` `gC`
                            WHERE `time` IS NOT NULL
                            AND `gC`.`sex` = '".$sex."'
                            AND `gC`.`competition_id` = '".$id."'
                        ) UNION (
                            SELECT `id`,`team_id`,`team_number`,
                            `person_1`,`person_2`,`person_3`,`person_4`,
                            99999999 AS `time`,`run`
                            FROM `scores_fs` `gD`
                            WHERE `time` IS NULL
                            AND `gD`.`sex` = '".$sex."'
                            AND `gD`.`competition_id` = '".$id."'
                        ) ORDER BY `time`
                    ) `gi`
                ) `g`

                INNER JOIN `teams` `t` ON `t`.`id` = `g`.`team_id`
                LEFT JOIN `persons` `p1` ON `g`.`person_1` = `p1`.`id`
                LEFT JOIN `persons` `p2` ON `g`.`person_2` = `p2`.`id`
                LEFT JOIN `persons` `p3` ON `g`.`person_3` = `p3`.`id`
                LEFT JOIN `persons` `p4` ON `g`.`person_4` = `p4`.`id`
                ORDER BY `time`
            ");

            if (count($scores)) {
                $overview[] = array('Feuerwehrstafette '.$name, count($scores));

                $wks = 4;

                $worksheet = $objPHPExcel->createSheet($worksheetCount);

                $worksheet->setTitle('FS '.$name);
                $worksheet->mergeCells('A1:F1');
                $worksheet->setCellValue('A1', 'Feuerwehrstafette '.$name);
                setExcelBold($worksheet, 'A1');
                setExcelHCenter($worksheet, 'A1');

                setExcelHead($worksheet, 'A3', 'Platz');
                setExcelHead($worksheet, 'B3', 'Mannschaft');
                setExcelHead($worksheet, 'C3', 'Zeit');

                for($line = 0; $line < count($scores); $line++) {
                    $score = $scores[$line];
                    $tr = $line + 4;
                    $scores[$line]['tr'] = $tr;

                    $team_number = '';
                    if ($score['team_number']) $team_number =  ' '.(intval($score['team_number'])+1);


                    $place = $line;
                    while (isset($scores[$place - 1]) && $scores[$place - 1]['time'] == $score['time']) {
                        $place = $place - 1;
                    }


                    $worksheet->setCellValueExplicit('A'.$tr, strval(($place+1).'.'));
                    setExcelHCenter($worksheet, 'A'.$tr);
                    setExcelBorder($worksheet, 'A'.$tr);

                    $worksheet->setCellValue('B'.$tr, $score['team'].$team_number.' '.$score['run']);
                    setExcelBorder($worksheet, 'B'.$tr);

                    setExcelTime($worksheet, 'C'.$tr, $score['time']);
                    setExcelBorder($worksheet, 'C'.$tr);
                }

                $chr = 68;
                foreach ($scores as $score) {
                    for ($i = 0; $i < $wks; $i++) {
                        $worksheet->setCellValue(chr($chr + $i).$score['tr'], $score['firstname'.($i+1)].' '.$score['name'.($i+1)]);
                        setExcelBorder($worksheet, chr(68 + $i).$score['tr']);
                    }
                }

                for ($i = 0; $i < $wks; $i++) {
                    setExcelHead($worksheet, chr($chr + $i).'3', 'WK'.($i+1));
                    $worksheet->getColumnDimension(chr($chr + $i))->setAutoSize(true);
                }

                foreach (array('A','B') as $letter) {
                    $worksheet->getColumnDimension($letter)->setAutoSize(true);
                }

                $worksheetCount++;
            }
        }





        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();

        $worksheet->setCellValue('A1', $competition['event']);
        $worksheet->mergeCells('A1:B1');

        $worksheet->setCellValue('A2', $competition['place']);
        $worksheet->mergeCells('A2:B2');

        setExcelBold($worksheet, 'A1', 18);
        setExcelBold($worksheet, 'A2', 18);
        setExcelHCenter($worksheet, 'A1');
        setExcelHCenter($worksheet, 'A2');

        $worksheet->setCellValue('A3', gdate($competition['date']));
        $worksheet->mergeCells('A3:B3');
        setExcelBold($worksheet, 'A3', 14);
        setExcelHCenter($worksheet, 'A3');

        $worksheet->setCellValue('A5', "Diese Datei enthält berechnete Ergebnisse von diesem Wettkampf. Es sind nicht die offiziellen Ergebnisse des Veranstalters. Die Daten wurden auf www.feuerwehrsport-statistik.de gesammelt. Bei Fehlern oder Anmerkungen bitte auf der Webseite melden.");
        $worksheet->mergeCells('A5:B9');
        $worksheet->getStyle('A5')->getAlignment()->setWrapText(true);
        setExcelHCenter($worksheet, 'A5');
        setExcelVCenter($worksheet, 'A5');


        $worksheet->setCellValue('A10', "Die Ergebnisse sind auf einzelene Tabellen verteilt. Bitte dazu am unteren Rand die jeweiligen Tabellen auswählen.");
        $worksheet->mergeCells('A10:B12');
        $worksheet->getStyle('A10')->getAlignment()->setWrapText(true);
        setExcelHCenter($worksheet, 'A10');
        setExcelVCenter($worksheet, 'A10');

        $line = 14;
        foreach ($overview as $row) {
            $worksheet->setCellValue('A'.$line, $row[0]);
            setExcelBorder($worksheet, 'A'.$line);
            $worksheet->setCellValue('B'.$line, $row[1]);
            setExcelBorder($worksheet, 'B'.$line);
            $line++;
        }

        $worksheet->getColumnDimension('A')->setWidth(40);
        $worksheet->getColumnDimension('B')->setWidth(20);


        Cache::put($objPHPExcel);
    }

    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Ergebnisse.xlsx"');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

    $objWriter->save('php://output');
}
