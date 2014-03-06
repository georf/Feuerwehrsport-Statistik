<?php

$person = Check2::page()->get('id')->isIn('persons', 'row');
$id = $person['id'];

TempDB::generate('x_full_competitions');
$teamsUnsorted = $db->getRows("
  SELECT `t`.*, COUNT(`i`.`team_id`) AS `count`,
    SUM(`i`.`hb`) AS `hb`,
    SUM(`i`.`hl`) AS `hl`,
    SUM(`i`.`gs`) AS `gs`,
    SUM(`i`.`fs`) AS `fs`,
    SUM(`i`.`la`) AS `la`
  FROM (
      SELECT `team_id`,
      1 AS `hb`, 0 AS `hl`, 0 AS `gs`, 0 AS `fs`, 0 AS `la`
      FROM `scores`
      WHERE `person_id` = '".$id."'
      AND `discipline` = 'HB'
    UNION ALL
      SELECT `team_id`,
      0 AS `hb`, 1 AS `hl`, 0 AS `gs`, 0 AS `fs`, 0 AS `la`
      FROM `scores`
      WHERE `person_id` = '".$id."'
      AND `discipline` = 'HL'
    UNION ALL
      SELECT `team_id`,
      0 AS `hb`, 0 AS `hl`, 1 AS `gs`, 0 AS `fs`, 0 AS `la`
      FROM `scores_gs`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
      OR `person_5` = '".$id."'
      OR `person_6` = '".$id."'
    UNION ALL
      SELECT `team_id`,
      0 AS `hb`, 0 AS `hl`, 0 AS `gs`, 0 AS `fs`, 1 AS `la`
      FROM `scores_la`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
      OR `person_5` = '".$id."'
      OR `person_6` = '".$id."'
      OR `person_7` = '".$id."'
    UNION ALL
      SELECT `team_id`,
      0 AS `hb`, 0 AS `hl`, 0 AS `gs`, 1 AS `fs`, 0 AS `la`
      FROM `scores_fs`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
  ) `i`
  INNER JOIN `teams` `t` ON `t`.`id` = `i`.`team_id`
  GROUP BY `team_id`
");
$teams = array();
foreach ($teamsUnsorted as $team) $teams[$team['id']] = $team;

$disciplines = array(
  array('hb', false),
  array('hl', false, 'male'),
  array('zk', false, 'male'),
  array('fs', true),
  array('gs', true,  'female'),
  array('la', true),
);
$scores = array();

echo Title::set(htmlspecialchars($person['firstname']).' '.htmlspecialchars($person['name']));
$toc = TableOfContents::get();
foreach ($disciplines as $disciplineConf) {
  $discipline = $disciplineConf[0];
  $scores[$discipline] = array();
  if (count($discipline) > 3 && $discipline[2] != $person['sex']) continue;

  if (in_array($discipline, array('hl', 'hb'))) {
    $scores[$discipline] = $db->getRows("
      SELECT
        `c`.`place_id`,`c`.`place`,
        `c`.`event_id`,`c`.`event`,
        `c`.`score_type_id`,
        `s`.`competition_id`,`c`.`date`,
        `s`.`time`,`s`.`team_id`,
        `s`.`id` AS `score_id`,`s`.`team_number`
      FROM `scores` `s`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE `person_id` = '".$id."'
      AND `discipline` LIKE '".$discipline."'
    ");
  } elseif ($discipline == 'zk') {
    $scores[$discipline] = $db->getRows("
      SELECT
        `c`.`place_id`,`c`.`place`,
        `c`.`event_id`,`c`.`event`,
        `c`.`score_type_id`,
        `hb`.`competition_id`,`c`.`date`,
        `hb`.`time` AS `hb`,
        `hl`.`time` AS `hl`,
        `hb`.`time` + `hl`.`time` AS `time`
      FROM (
        SELECT `time`,`competition_id`
        FROM `scores`
        WHERE `person_id` = '".$id."'
        AND `discipline` = 'HB'
        AND `time` IS NOT NULL
        ORDER BY `time`
      ) `hb`
      INNER JOIN (
        SELECT `time`,`competition_id`
        FROM `scores`
        WHERE `person_id` = '".$id."'
        AND `discipline` = 'HL'
        AND `time` IS NOT NULL
        ORDER BY `time`
      ) `hl` ON `hl`.`competition_id` = `hb`.`competition_id`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `hb`.`competition_id`
      GROUP BY `c`.`id`
    ");
  } elseif ($discipline == 'gs') {
    $scores[$discipline] = $db->getRows("
      SELECT
        `c`.`place_id`,`c`.`place`,
        `c`.`event_id`,`c`.`event`,
        `c`.`score_type_id`,
        `s`.`competition_id`,`c`.`date`,
        `s`.`time`,`s`.`team_id`,
        `s`.`id` AS `score_id`,`s`.`team_number`,
        `s`.`person_1`,`s`.`person_2`,`s`.`person_3`,`s`.`person_4`,`s`.`person_5`,`s`.`person_6`
      FROM `scores_gs` `s`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
      OR `person_5` = '".$id."'
      OR `person_6` = '".$id."'
    ");
  } elseif ($discipline == 'la') {
    $scores[$discipline] = $db->getRows("
      SELECT
        `c`.`place_id`,`c`.`place`,
        `c`.`event_id`,`c`.`event`,
        `c`.`score_type_id`,
        `s`.`competition_id`,`c`.`date`,
        `s`.`time`,`s`.`team_id`,
        `s`.`id` AS `score_id`,`s`.`team_number`,
        `s`.`person_1`,`s`.`person_2`,`s`.`person_3`,`s`.`person_4`,`s`.`person_5`,`s`.`person_6`,`s`.`person_7`
      FROM `scores_la` `s`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
      OR `person_5` = '".$id."'
      OR `person_6` = '".$id."'
      OR `person_7` = '".$id."'
    ");
  } elseif ($discipline == 'fs') {
    $scores[$discipline] = $db->getRows("
      SELECT
        `c`.`place_id`,`c`.`place`,
        `c`.`event_id`,`c`.`event`,
        `c`.`score_type_id`,
        `s`.`competition_id`,`c`.`date`,
        `s`.`time`,`s`.`team_id`,
        `s`.`id` AS `score_id`,`s`.`team_number`,
        `s`.`person_1`,`s`.`person_2`,`s`.`person_3`,`s`.`person_4`
      FROM `scores_fs` `s`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE `person_1` = '".$id."'
      OR `person_2` = '".$id."'
      OR `person_3` = '".$id."'
      OR `person_4` = '".$id."'
    ");
  }

  if (count($scores[$discipline])) {
    $toc->link($discipline, FSS::dis2name($discipline));
  }
}
$toc->link('team', 'Mannschaft');
$toc->link('fehler', 'Fehler melden');

$teamLogos = '';
foreach ($teams as $team) {
  $teamLogos .= TeamLogo::getTall($team['logo'], $team['short'], '<div class="logo-replacement">'.$team['short'].'</div>');
}
echo Bootstrap::row()
  ->col(Chart::img('person_overview', array($id), true, 'person_overview'), 3)
  ->col($teamLogos, 6)
  ->col($toc, 3);


foreach ($disciplines as $disciplineConf) {
  $discipline = $disciplineConf[0];
  $group      = $disciplineConf[1];
  if (count($scores[$discipline]) === 0) continue;
  $name = FSS::dis2name($discipline);

  $sum  = 0;
  $i    = 0;
  $best = PHP_INT_MAX;
  $bad  = 0;

  foreach ($scores[$discipline] as $score) {
    if (FSS::isInvalid($score['time'])) continue;
    $sum += $score['time'];
    $i++;
    $best = min($best, $score['time']);
    $bad  = max($bad, $score['time']);
  }
  echo Title::h2($name, $discipline);
  $chartTable = ChartTable::build();
  if (!FSS::isInvalid($best)) $chartTable->row('Bestzeit:', FSS::time($best).' s');
  if (!FSS::isInvalid($bad)) $chartTable->row('Schlechteste Zeit:', FSS::time($bad).' s');
  $chartTable->row('Zeiten:', count($scores[$discipline]));
  if ($i > 0) $chartTable->row('Durchschnitt:', FSS::time($sum/$i).' s');
  if ($discipline != 'zk') $chartTable->row(Chart::img('person_bad_good', array($id, $discipline)));
  
  $row = Bootstrap::row()->col($chartTable, 3);
  if ($i > 0) $row->col(Chart::img('person', array($id, $discipline)), 3);
  echo $row;

  $countTable = CountTable::build($scores[$discipline], array('datatable-'.$discipline))
  ->col('Datum', 'date', 8)
  ->col('Typ', function($row) { return Link::event($row['event_id'], $row['event']); }, 15)
  ->col('Ort', function($row) { return Link::place($row['place_id'], $row['place']); }, 15);

  if (in_array($discipline, array('hl', 'hb'))) {
    $countTable
    ->addClass('single-scores')
    ->rowAttribute('data-id', 'score_id')
    ->col('Mannschaft', function($row) use ($teams) { 
      if ($row['team_id']) {
        $t_name = $teams[$row['team_id']]['name'];
        if ($row['score_type_id']) {
          $t_name .= FSS::teamNumber($row['team_number'], $row['competition_id'], $row['team_id'], false, ' ');
        }
        return Link::team($row['team_id'], $t_name);
      }
      return '';
    }, 20, array('class' => 'team'));
  } elseif ($discipline == 'zk') {
    $countTable
    ->col('HB', function($row) { return FSS::time($row['hb']); }, 5)
    ->col('HL', function($row) { return FSS::time($row['hb']); }, 5);
  }
  $countTable->col('Zeit', function($row) { return FSS::time($row['time']); }, 7, array('class' => 'number'));
  if ($group) {
    $countTable->col('Position', function($row) use ($id, $discipline, $person) {
      for ($wk = 1; $wk < 8; $wk++) {
        if (array_key_exists('person_'.$wk, $row) && $row['person_'.$wk] == $id) {
          return WK::type($wk, $person['sex'], $discipline);
        }
      }
    }, 10);
  }
  $countTable->col('', function($row) { return Link::competition($row['competition_id'], 'Details'); }, 6);
  echo Bootstrap::row()->col($countTable, 12);

  if (in_array($discipline, array('hl', 'hb'))) {
    echo '<h3 style="clear:both">'.$name.' - Vergleich der Bestzeiten mit anderen Sportler</h3>';
    echo '<p class="chart">'.Chart::img('person_best_score', array($id, $discipline)).'</p>';
  }

  if ($group) {
    // search for team mates
    $teammates = array();

    foreach ($scores[$discipline] as $score) {
      for ($wk = 1; $wk < 8; $wk++) {
        if (array_key_exists('person_'.$wk, $score) && $score['person_'.$wk] != null && $score['person_'.$wk] != $id) {
          if (!array_key_exists($score['person_'.$wk], $teammates)) $teammates[$score['person_'.$wk]] = array();
          $teammates[$score['person_'.$wk]][] = $score['competition_id'];
        }
      }
    }

    if (count($teammates) > 0) {
      echo '<h3 style="clear:both">'.$name.' - Mannschaftsmitglieder</h3>';
      echo Bootstrap::row()->col(CountTable::build($teammates, array('datatable-teammates'))
      ->col('Person', function($row, $id) { return Link::person($id, 'full'); }, 5)
      ->col('Läufe', function($row) { return count($row); }, 1, array(), array('class' => 'small'))
      ->col('Wettkämpfe', function($competitionIds) {
        $competitionIds = array_unique($competitionIds);
        $competitions = array();
        foreach ($competitionIds as $id) {
          $competition = FSS::competition($id);
          $competitions[] = Link::competition($id,
            $competition['place'].'`'.date('y', strtotime($competition['date'])),
            $competition['event'].' - '.gDate($competition['date'])
          );
        }
        return implode(', ', $competitions);
      }, 20, array('class' => 'small')), 12);
    }
    echo '<h3 style="clear:both">'.$name.' - Gelaufene Positionen</h3>';
    echo Chart::img('position_'.$discipline, array($id));
  }
}

if (count($teams)) {
  echo Title::h2('Mannschaft', 'mannschaft');
  foreach ($teams as $team) {
    $elems = array();
    foreach (array('hl','hb','gs','la','fs') as $key) {
      if ($team[$key] > 0) {
        $elems[] = $team[$key].'x '.FSS::dis2name($key);
      }
    }
    echo Bootstrap::row()
      ->col(TeamLogo::getTall($team['logo'], $team['short'], '<div class="logo-replacement">'.$team['short'].'</div>'), 2)
      ->col('<h3>'.htmlspecialchars($team['name']).'</h3>', 6)
      ->col('<ul><li>'.Link::team($team['id'], 'Details').'</li><li>'.$team['count'].' gelaufene Zeiten</li><li>'.implode('</li><li>', $elems).'</li></ul>', 4);
    }
}

echo Title::h2('Fehler melden', 'fehler');
echo Bootstrap::row()
  ->col('<p>Beim Importieren der Ergebnisse kann es immer wieder mal zu Fehlern kommen. Geraden wenn die Namen in den Ergebnislisten verkehrt geschrieben wurden, kann keine eindeutige Zuordnung stattfinden. Außerdem treten auch Probleme mit Umlauten oder anderen besonderen Buchstaben im Namen auf.</p>'.
        '<p>Ihr könnt jetzt beim Korrigieren der Daten helfen. Dafür klickt ihr auf folgenden Link und generiert eine Meldung für den Administrator. Dieser überprüft dann die Eingaben und leitet weitere Schritte ein.</p>'.
        '<p><button id="report-error" data-person-id="'.$id.'">Fehler mit dieser Person melden</button></p>', 12);