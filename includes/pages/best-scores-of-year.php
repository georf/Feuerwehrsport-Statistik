<?php

$year = Check2::page()->get('id')->match('|^[1,2][0-9]{3}$|');

echo Title::set('Überblick der Bestzeiten im Jahr '.$year);

echo Bootstrap::row()
->col('<p>Die Tabellen zeigen die gesammelten Bestzeiten für das Jahr '.$year.' in den Einzeldisziplinen. Einen Überblick über das Jahr gibt es '.Link::year($year, 'hier').'.</p>', 5)

->col('<ul>'.
      '<li>'.Link::year($year, 'Jahresübersicht').'</li>'.
      '<li>'.Link::bestPerformanceOfYear($year, 'Bestleistungen des Jahres').'</li>'.
      '</ul>', 5);

$navTab = Bootstrap::navTab('best-of-year');
$disciplines = array(
  array('hb', false, 'female'),
  array('hb', false, 'male'),
  array('hl', false, 'male'),
  array('gs', true,  false),
  array('la', true,  'female'),
  array('la', true,  'male'),
);

foreach ($disciplines as $d) {
  $dis = function ($d, $year) use (&$navTab, &$db) {
    $discipline = $d[0];
    $group      = $d[1];
    $sex        = $d[2];

    if (!$group) {
      $best = $db->getRows("
        SELECT * 
        FROM (
          SELECT  `s` . * ,  `e`.`name` AS  `event` , 
            `p`.`name`, `p`.`firstname`, `p`.`sex`, `c`.`date`
          FROM  `scores`  `s` 
          INNER JOIN  `competitions`  `c` ON  `c`.`id` =  `s`.`competition_id` 
          INNER JOIN  `events`  `e` ON  `e`.`id` =  `c`.`event_id` 
          INNER JOIN  `persons`  `p` ON  `p`.`id` =  `s`.`person_id` 
          WHERE YEAR(`c`.`date`) = '".$db->escape($year)."'
          AND `discipline` = '".$discipline."'
          AND `p`.`sex` = '".$sex."'
          AND  `time` IS NOT NULL 
          ORDER BY  `s`.`time`
        ) `inner` 
        GROUP BY  `person_id` 
        ORDER BY  `time`
      ");
    } else {
      $best = $db->getRows("
        SELECT * 
        FROM (
          SELECT  `s` . * ,  `e`.`name` AS  `event` , 
            `t`.`name`, `c`.`date`
          FROM  `scores_".$discipline."`  `s` 
          INNER JOIN  `competitions`  `c` ON  `c`.`id` =  `s`.`competition_id` 
          INNER JOIN  `events`  `e` ON  `e`.`id` =  `c`.`event_id` 
          INNER JOIN  `teams`  `t` ON  `t`.`id` =  `s`.`team_id` 
          WHERE YEAR(`c`.`date`) = '".$db->escape($year)."'
          ".($sex? " AND `s`.`sex` = '".$sex."' ":"")."
          AND  `time` IS NOT NULL 
          ORDER BY  `s`.`time`
        ) `inner` 
        GROUP BY  `team_id` 
        ORDER BY  `time`
      ");
    }

    $i = 0;
    $output = CountTable::build($best)
    ->col('Platz', function ($row) use (&$i) { $i++; return $i.'.'; }, 5)
    ->col('Name', function($row) use ($group) { 
      return ($group)? 
        Link::team($row['id'], $row['name']) 
      : Link::fullPerson($row['person_id'], $row['name'], $row['firstname']); 
    }, 20)
    ->col('Bestzeit', function($row) { return FSS::time($row['time']); }, 10)
    ->col('Wettkampf', function($row) {
      return Link::competition($row['competition_id'], $row['event'].' - '.gDate($row['date']));
    }, 40);
    $headline = FSS::dis2img($discipline).' '.strtoupper($discipline);
    if ($sex) $headline .= ' '.FSS::sex($sex);
    $navTab->tab($headline, Bootstrap::row()->col($output, 12), FSS::dis2name($discipline));
  };
  $dis($d, $year);
}

echo $navTab;
