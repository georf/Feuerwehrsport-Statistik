<?php

$_discipline = 1;
$_sex = 'male';

if (isset($_GET['discipline']) && is_numeric($_GET['discipline'])) {
  $_discipline = $_GET['discipline'];
}

if (isset($_GET['sex'])) {
  $_sex = $_GET['sex'];
}


$years = $db->getRows("
    SELECT YEAR(`date`) AS `year`
    FROM `competitions`
    GROUP BY YEAR(`date`)
    ORDER BY `year`
");


$open   = array();
$close  = array();
$max    = array();
$min    = array();
$median = array();
$label  = array();
$ave    = array();
$ave5   = array();


foreach ($years as $year) {
    $competitions = $db->getRows("
      SELECT `c`.*,`p`.`name` AS `place`
      FROM `competitions` `c`
      INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
      WHERE YEAR(`c`.`date`) = '".$year['year']."'
      ORDER BY `c`.`date`;
    ");

    $times = array();

    foreach ($competitions as $competition) {
        $scores = $db->getRows("
            SELECT `time`
            FROM (
              SELECT `time`
              FROM (
                SELECT `s`.*
                FROM `scores` `s`
                INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                WHERE `s`.`competition_id` = '".$competition['id']."'
                AND `s`.`discipline_id` = '".$db->escape($_discipline)."'
                AND `p`.`sex` = '".$db->escape($_sex)."'
                AND `s`.`time` IS NOT NULL
                ORDER BY `s`.`time`) `i`
              GROUP BY `i`.`person_id`
            ) `i2`
            ORDER BY `i2`.`time`
        ");

        if (!$scores || count($scores) < 0) {
            continue;
        }

        foreach ($scores as $score) {
            $times[] = $score['time'];
        }
    }

    if (!$times || count($times) < 0) {
        continue;
    }
    $n = count($times);


    $sum = 0;
    $sum5 = 0;
    $i = 0;
    foreach ($times as $time) {
        $i++;
        $sum += $time;
        if ($i == 5) {
            $sum5 = $sum;
        }
    }

    $ave[] = c2s(round($sum/$n,2));

    if ($sum5 == 0) {
        $ave5[] = $sum;
    } else {
        $ave5[] = c2s(round($sum5/5,2));
    }

    sort($times);

    $min[] = c2s($times[0]);
    $max[] = c2s($times[$n-1]);
    $open[] = c2s($times[round(0.25 * ($n+1))-1]);
    $close[] = c2s($times[round(0.75 * ($n+1))-1]);

    if ($n%2 == 0) {
        $median[] = c2s(($times[$n/2]+$times[$n/2+1])/2);
    } else {
        $median[] = c2s($times[($n+1)/2]);
    }

    $label[] = $year['year'];
}

ChartLoader::stockChart(array(
    'Open' => $open,
    'Close' => $close,
    'Min' => $min,
    'Max' => $max,
    'Median' => $median,
    'Durchschnitt' => $ave,
    'Beste 5' => $ave5,
    'labels' => $label,
), ChartLoader::discipline2text($_discipline, $_sex));
