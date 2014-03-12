<?php

TempDB::generate('x_full_competitions');

$disciplines = array(
  'hbf' => array('hb', 'female', true),
  'hbm' => array('hb', 'male', true),
  'hl' => array('hl', 'male', false),
  'zk' => array('zk', 'male', false)
);
$year = Check2::page()->get('id')->match('|^[1,2][0-9]{3}$|');
$dcup = $db->getFirstRow("SELECT * FROM `dcups` WHERE `year` = '".$year."' LIMIT 1");
Check2::page()->isTrue($dcup);
$id = $dcup['id'];
$key = Check2::page()->get('id2')->present();
Check2::page()->isTrue(isset($disciplines[$key]));

$discipline = $disciplines[$key][0];
$sex = $disciplines[$key][1];
$headline = FSS::dis2name($discipline);
$year = $dcup['year'];
if ($disciplines[$key][2]) $headline .= ' - '.FSS::sex($sex);

$persons = $db->getRows("
  SELECT `name`, `firstname`, `points`, `position`, `person_id`
  FROM `persons` `p`
  INNER JOIN `dcup_points` `d` ON `d`.`person_id` = `p`.`id`
  WHERE `d`.`discipline` = '".$discipline."'
  AND `d`.`dcup_id` = '".$id."'
  AND `p`.`sex` = '".$sex."'
  ORDER BY `position`
");

if ($discipline == 'zk') {
  $competitions = $db->getRows("
    SELECT `competition_id`,`place`,`place_id`,`date`, COUNT(`person_id`) AS `count`
    FROM `scores_dcup_zk` `d`
    INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `d`.`competition_id`
    WHERE `d`.`dcup_id` = '".$id."'
    GROUP BY `d`.`competition_id`
    ORDER BY `c`.`date`
  ");
} else {
  $competitions = $db->getRows("
    SELECT `competition_id`,`place`,`place_id`,`date`, COUNT(`person_id`) AS `count`
    FROM `scores_dcup_single` `d`
    INNER JOIN `scores` `s` ON `s`.`id` = `d`.`score_id`
    INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
    INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
    WHERE `d`.`dcup_id` = '".$id."'
    AND `s`.`discipline` = '".$discipline."'
    AND `p`.`sex` = '".$sex."'
    GROUP BY `s`.`competition_id`
    ORDER BY `c`.`date`
  ");
}

$links = array();
foreach ($competitions as $c) {
  $links[] = 
      Link::competition($c['competition_id'], gDate($c['date']).' - '.$c['place']).
      '<br/>'.$c['count'].' Wettkämpfer';
}

foreach ($persons as $key => $person) {
  $persons[$key]['scores'] = DcupCalculation::getSingleScores($person['person_id'], $id, $discipline);
}


$zweikampf = ($discipline == 'zk')? ' Die Zweikampfwertung wurde erst <b>2013</b> offiziell eingeführt.' : '';

echo Title::set($headline.' - Deutschlandpokal '.$year);

echo DcupCalculation::notReadyBox($dcup);

echo Bootstrap::row()
->col(FSS::dis2img($discipline, 'middle'), 2)
->col(
  '<p>Diese Seite zeigt die Gesamtwertung der D-Cup-Einzelergebnisse in der Kategorie »<em>'.$headline.'</em>«. Dabei handelt es sich um selbst berechnete Daten, welche <b>nicht offiziell</b> sind.'.$zweikampf.
  '<br/>Zu der Gesamtwertung zitiere ich die Ausschreibung des DFV:</p>'.
  '<img style="float:left; height:100px;padding-right: 30px;" src="/styling/images/dfv.png"/><p style="font-style:italic;">Bei Punktgleichheit von Wettkämpfern entscheidet die bessere Gesamtzeit der Bestzeiten aus den einzelnen Wettkämpfen über die bessere Platzierung. Hat ein Wettkämpfer eine geringere Anzahl von Wettkampfteilnahmen, ist er bei gleicher Gesamtpunktzahl automatisch hinter dem mit mehr Wettkämpfen platziert.</p>'
, 6)
->col('<ul><li>'.Link::dcup($year).'</li></ul><hr/><ul><li>'.implode('</li><li>', $links).'</li></ul>', 4);

$countTable = CountTable::build($persons)
->col('', 'position', 5)
->col('Name', 'name', 30)
->col('Vorame', 'firstname', 30);

foreach ($competitions as $competition) {
  $countTable->col($competition['place'], function ($row) use ($competition) {
    foreach ($row['scores'] as $score) {
      if ($score['competition_id'] == $competition['competition_id']) {
        $time = FSS::time($score['time']);
        if ($score['points'] > 0) {
            $time .= ' ('.$score['points'].')';
        }
        return $time;
      }
    }
  }, 18, ($discipline == 'zk')?
    array('title' => function ($row) use ($competition) {
      foreach ($row['scores'] as $score) {
        if ($score['competition_id'] == $competition['competition_id']) {
          return 'HL: '.FSS::time($score['hl']).' HB: '.FSS::time($score['hb']);
        }
      }
    })
    : 
    array() 
  , array('class' => 'small'));
}

$countTable
->col('Bestzeit', function ($row) { return FSS::time($row['scores'][0]['time']); }, 10, array(), array('class' => 'small'))
->col('Teil.',  function ($row) { return count($row['scores']); }, 7, array(), array('class' => 'small'))
->col('Punkte', 'points', 7, array(), array('class' => 'small'))
->col('', function ($row) { return Link::person($row['person_id']); }, 9);

echo $countTable;