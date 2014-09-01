<?php

echo Title::set('Die 100 schnellsten Zeiten');

echo Bootstrap::row()
->col('<p>Die Tabellen zeigen die 100 besten Zeiten. Dies sind keine Rekorde, da auch unbestätigte Wettkämpfe zu finden sind.</p>', 5)

->col('<ul>'.
      '<li>'.Link::years('Übersicht über die Jahre').'</li>'.
      '<li>'.Link::competitions('Alle Wettkämpfe').'</li>'.
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
  $dis = function ($d) use (&$navTab, &$db) {
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
          WHERE `discipline` = '".$discipline."'
          AND `p`.`sex` = '".$sex."'
          AND `p`.`nation_id` = 1
          AND  `time` IS NOT NULL 
          ORDER BY  `s`.`time`
        ) `inner` 
        GROUP BY  `person_id` 
        ORDER BY  `time`
        LIMIT 100
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
          WHERE `time` IS NOT NULL
          ".($sex? " AND `s`.`sex` = '".$sex."' ":"")."
          ORDER BY  `s`.`time`
        ) `inner` 
        GROUP BY  `team_id` 
        ORDER BY  `time`
        LIMIT 100
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
  $dis($d);
}

echo $navTab;
