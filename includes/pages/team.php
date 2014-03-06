<?php

$team = Check2::page()->get('id')->isIn('teams', 'row');
$id = $team['id'];


TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');
TempDB::generate('x_full_competitions');


$sexConfig = array(
  'female' => 'weiblich',
  'male' => 'männlich',
);

$members = array();
$member = array(
  'HB' => 0,
  'GS' => 0,
  'LA' => 0,
  'FS' => 0,
  'HL' => 0,
  'mem_id' => null
);

$scores = $db->getRows("
  SELECT `person_id`,`discipline`
  FROM `scores`
  WHERE `team_id` = '".$id."'
  AND `discipline` = 'HB'
");
foreach ($scores as $score) {
  $pid = $score['person_id'];
  if (!isset($members[$pid])) $members[$pid] = $member;
  $members[$pid][$score['discipline']]++;
}

$scores = $db->getRows("
  SELECT `discipline`,`person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`
  FROM (
    SELECT 'GS' AS `discipline`,`person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,NULL AS `person_7`
    FROM `scores_gs`
    WHERE `team_id` = '".$team['id']."'
  UNION
    SELECT 'LA' AS `discipline`,`person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`
    FROM `scores_la`
    WHERE `team_id` = '".$team['id']."'
  UNION
    SELECT 'FS' AS `discipline`,`person_1`,`person_2`,`person_3`,`person_4`,NULL AS `person_5`,NULL AS `person_6`, NULL AS `person_7`
    FROM `scores_fs`
    WHERE `team_id` = '".$team['id']."'
  ) `i`
");
foreach ($scores as $score) {
  for($i = 1; $i <= 7; $i++) {
    if (empty($score['person_'.$i])) continue;

    $pid = $score['person_'.$i];
    if (!isset($members[$pid])) $members[$pid] = $member;
    $members[$pid][$score['discipline']]++;
  }
}

foreach ($members as $pid=>$member) {
  $m = $db->getFirstRow("
    SELECT `name`, `firstname`, `sex`
    FROM `persons`
    WHERE `id` = '".$pid."'
    LIMIT 1;
  ");
  $members[$pid]['firstname'] = $m['firstname'];
  $members[$pid]['name'] = $m['name'];
  $members[$pid]['sex'] = $m['sex'];
  $members[$pid]['id'] = $pid;
}

$teamDisciplines = array(
  'GS' => array(),
  'FS' => $sexConfig,
  'LA' => $sexConfig,
);
$teamDisciplines['GS'] = $db->getRows("
  SELECT `s`.*,
    `event_id`, `event`,
    `place_id`, `place`,
    `date`, '' AS `type`
  FROM `scores_gs` `s`
  INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
  WHERE `s`.`team_id` = '".$id."'
");
foreach ($sexConfig as $sex => $name) {
  $teamDisciplines['FS'][$sex] = $db->getRows("
    SELECT `s`.*,
      `event_id`, `event`,
      `place_id`, `place`,
      `date`, `fs` AS `type`
    FROM `scores_fs` `s`
    INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
    WHERE `s`.`team_id` = '".$id."'
    AND `s`.`sex` = '".$sex."'
  ");
  $teamDisciplines['LA'][$sex] = $db->getRows("
    SELECT `s`.*,
      `event_id`, `event`,
      `place_id`, `place`,
      `date`, `la` AS `type`
    FROM `scores_la` `s`
    INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
    WHERE `s`.`team_id` = '".$id."'
    AND `s`.`sex` = '".$sex."'
  ");
}

// Mannschaftswertung
$team_scores = array(
  'hb-female' => array(),
  'hb-male' => array(),
  'hl' => array(),
);

$competitions = $db->getRows("
  SELECT `c`.*
  FROM `x_full_competitions` `c`
  WHERE `c`.`score_type_id` IS NOT NULL
  AND EXISTS (
    SELECT 1 
    FROM `scores` 
    WHERE `team_id` = '".$id."' 
    AND `competition_id` = `c`.`id` 
    LIMIT 1
  )
");

$single_disciplines = array(
  'x_scores_hbf' => 'hb-female',
  'x_scores_hbm' => 'hb-male',
  'x_scores_hl' => 'hl',
);
foreach ($competitions as $competition) {
  foreach ($single_disciplines as $table => $discipline) {
    $scores = $db->getRows("
      SELECT `best`.*,
        `p`.`firstname`, `p`.`name`
      FROM (
        SELECT *
        FROM (
          (
            SELECT `id`,`team_number`,
            `person_id`,
            `time`
            FROM `".$table."`
            WHERE `time` IS NOT NULL
            AND `competition_id` = '".$competition['id']."'
            AND `team_number` >= 0
            AND `team_id` = '".$id."'
          ) UNION (
            SELECT `id`,`team_number`,
            `person_id`,
            ".FSS::INVALID." AS `time`
            FROM `".$table."`
            WHERE `time` IS NULL
            AND `competition_id` = '".$competition['id']."'
            AND `team_number` >= 0
            AND `team_id` = '".$id."'
          ) ORDER BY `time`
        ) `all`
        GROUP BY `person_id`
      ) `best`
      INNER JOIN `persons` `p` ON `p`.`id` = `best`.`person_id`
      ORDER BY `time`
    ");
    
    if (!count($scores)) continue;

    $teams = array();
    foreach ($scores as $score) {
      $uniqueTeam = $score['team_number'];
      if (!isset($teams[$uniqueTeam])) {
        $teams[$uniqueTeam] = array(
          'number' => $score['team_number'],
          'scores' => array(),
          'time' => FSS::INVALID,
          'time68' => -1,
        );
      }
      $teams[$uniqueTeam]['scores'][] = $score;
    }

    // sort every persons in teams
    foreach ($teams as $uniqueTeam => $teamResult) {
      $time = 0;
      $time68 = 0;

      usort($teamResult['scores'], function($a, $b) {
        if ($a['time'] == $b['time']) return 0;
        elseif ($a['time'] > $b['time']) return 1;
        else return -1;
      });

      if (count($teamResult['scores']) < $competition['score']) {
        $teams[$uniqueTeam]['time'] = FSS::INVALID;
        $teams[$uniqueTeam]['time68'] = FSS::INVALID;
        continue;
      }

      for ($i = 0; $i < $competition['score']; $i++) {
        if ($teamResult['scores'][$i]['time'] == FSS::INVALID) {
          $teams[$uniqueTeam]['time'] = FSS::INVALID;
          $teams[$uniqueTeam]['time68'] = FSS::INVALID;
          continue 2;
        }
        $time += $teamResult['scores'][$i]['time'];
      }

      if (count($teamResult['scores']) < 6) {
        $teams[$uniqueTeam]['time68'] = FSS::INVALID;
      } else {
        for ($i = 0; $i < 6; $i++) {
          if ($teamResult['scores'][$i]['time'] == FSS::INVALID) {
            $teams[$uniqueTeam]['time68'] = FSS::INVALID;
            break;
          }
          $time68 += $teamResult['scores'][$i]['time'];
        }

        if ($teams[$uniqueTeam]['time68'] == -1) {
          $teams[$uniqueTeam]['time68'] = $time68;
        }
      }
      $teams[$uniqueTeam]['time'] = $time;
    }

    $team_scores[$discipline][] = array(
      'competition' => $competition,
      'teams' => $teams,
    );
  }
}


$competitions = $db->getRows("
  SELECT `id`,`date`, `event_id`, `event`, `place_id`, `place`,
    SUM(`i`.`single`) AS `single`,
    SUM(`i`.`gs`) AS `gs`,
    SUM(`i`.`la`) AS `la`,
    SUM(`i`.`fs`) AS `fs`
  FROM (
    SELECT `competition_id`,COUNT(*) AS `single`,0 AS `gs`,0 AS `la`,0 AS `fs`
    FROM `scores`
    WHERE `team_id` = '".$id."'
    GROUP BY `competition_id`
  UNION
    SELECT `competition_id`,0 AS `single`,COUNT(*) AS `gs`,0 AS `la`,0 AS `fs`
    FROM `scores_gs`
    WHERE `team_id` = '".$id."'
    GROUP BY `competition_id`
  UNION
    SELECT `competition_id`,0 AS `single`,0 AS `gs`,COUNT(*) AS `la`,0 AS `fs`
    FROM `scores_la`
    WHERE `team_id` = '".$id."'
    GROUP BY `competition_id`
  UNION
    SELECT `competition_id`,0 AS `single`,0 AS `gs`,0 AS `la`,COUNT(*) AS `fs`
    FROM `scores_fs`
    WHERE `team_id` = '".$id."'
    GROUP BY `competition_id`
  ) `i`
  INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `i`.`competition_id`
  GROUP BY `competition_id`
");

echo Bootstrap::row()
->col(TeamLogo::getTall($team['logo'], $team['short']), 2)
->col(
  Title::set($team['name']).
  '<table>'.
    '<tr><th>Mitglieder:</th><td>'.count($members).'</td></tr>'.
    '<tr><th>Webseite:</th><td>'.
      implode("<br/>", Link::linksForTeam($id)).
      Link::actionIcon('applications-internet-add', 'add-link', 'Link hinzufügen', array(
        'for-id' => $id,
        'for-table' => 'team',
      )).
    '</td></tr>'.
    '<tr><th>Bundesland/Land:</th><td>'.
      FSS::stateToText($team['state']).' '.
      Link::actionIcon('configure', 'select-state', 'Bundesland auswählen', array(
        'for-id' => $id,
        'for-type' => 'team',
        'current' => $team['state'],
      )).
    '</td></tr>'.
  '</table>'  
  , 8)
->col(Chart::img('team_sex', array($id)), 2);


echo Title::h2('Wettkämpfer');
echo Bootstrap::row()->col(CountTable::build($members, array("datatable-sort-members"))
->col('Name', 'name', 22)
->col('Vorname', 'firstname', 22)
->col('Geschlecht', function ($row) { return FSS::sex($row['sex']); }, 12)
->col('HB', 'HB', 5)
->col('GS', 'GS', 5)
->col('LA', 'LA', 5)
->col('FS', 'FS', 5)
->col('HL', 'HL', 5)
->col('', function ($row) { return Link::person($row['id'], 'Details', $row['name'], $row['firstname']); }, 8)
, 12);

echo Title::h2('Wettkämpfe');
echo Bootstrap::row()->col(CountTable::build($competitions, array("datatable-sort-competitions"))
->col('Datum', 'date', 13)
->col('Typ', function ($row) { return Link::event($row['event_id'], $row['event']); }, 28)
->col('Ort', function ($row) { return Link::place($row['place_id'], $row['place']); }, 28)
->col('Einzel', 'single', 8)
->col('GS', 'gs', 8)
->col('LA', 'la', 8)
->col('FS', 'fs', 8)
->col('', function ($row) { return Link::competition($row['id']); }, 10)
, 12);

// Mannschaftswertung
foreach ($team_scores as $fullKey => $tscores) {
  if (!count($tscores)) continue;

  $keys = explode('-', $fullKey);
  $key = $keys[0];
  $sex = false;
  if (count($keys) > 1) $sex = $keys[1];

  $title = FSS::dis2name($key);
  if ($sex) $title .= ' '.FSS::sex($sex);
  $title .= ' - Mannschaftswertung';
  echo Title::h2($title);

  $allScores = array();
  $all = array();
  $best68 = PHP_INT_MAX;
  foreach ($tscores as $tscore) {
    $personCount = $tscore['competition']['score'];
    if (!isset($all[$personCount])) {
      $all[$personCount] = array(
        'scores' => array(),
        'count' => 0
      );
    }

    foreach ($tscore['teams'] as $teamScore) {
      $teamScore['competition'] = $tscore['competition'];
      $inScore = array();
      $outScore = array();
      $i = 0;
      foreach ($teamScore['scores'] as $score) {
        $link = Link::person($score['person_id'], 'sub', $score['name'], $score['firstname'], FSS::time($score['time']));
        if ($i < $tscore['competition']['score']) $inScore[] = $link;
        else $outScore[] = $link;
        $i++;
      }
      $teamScore['personsIn'] = $inScore;
      $teamScore['personsOut'] = $outScore;
      $allScores[] = $teamScore;
      $all[$personCount]['count']++;
      if (FSS::isInvalid($teamScore['time'])) continue;
      $all[$personCount]['scores'][] = $teamScore['time'];
      if (!FSS::isInvalid($teamScore['time68']) && $teamScore['time68'] < $best68) {
        $best68 = $teamScore['time68'];
      }
    }
  }
  ksort($all);

  $chartTable = ChartTable::build();

  foreach ($all as $score => $b) {
    $chartTable->row($score.' Wertungen ('.$b['count'].' Zeiten)');
    $scores = $b['scores'];
    if (!count($scores)) continue;
    $chartTable
      ->row('Bestzeit:', FSS::time(min($scores)).' s')
      ->row('Durchschnitt:', FSS::time(array_sum($scores)/count($scores)).' s');
  }
  if ($best68 != PHP_INT_MAX) $chartTable->row('Bei 6 Läufern:', FSS::time($best68));

  echo Bootstrap::row()
  ->col($chartTable, 3)
  ->col(Chart::img('team_scores_team', array($id, $fullKey)), 3);

  echo Bootstrap::row()->col(CountTable::build($allScores, array("datatable-sort-team-scores", "table-small"))
  ->col('Wettkampf', function ($row) { return $row['competition']['date'].'<br/>'.Link::competition($row['competition']['id'], $row['competition']['event'], $row['competition']['place']); }, 7)
  ->col('Zeit', function ($row) { return FSS::time($row['time']); }, 5)
  ->col('bei 6', function ($row) { return FSS::time($row['time68']); }, 5)
  ->col('Wertung', function ($row) { return implode(', ', $row['personsIn']); }, 28)
  ->col('Außerhalb', function ($row) { return implode(', ', $row['personsOut']); }, 21, array('class' => 'small'))
  ->col('', function ($row) { return $row['competition']['score']; }, 3)
  , 12);
}


$teamScores = array(
   array('gs', false, $teamDisciplines['GS'],              6, array('')),
   array('fs', 'female', $teamDisciplines['FS']['female'], 4, array('feuer', 'abstellen')),
   array('fs', 'male', $teamDisciplines['FS']['male'],     4, array('feuer', 'abstellen')),
   array('la', 'female', $teamDisciplines['LA']['female'], 7, array('wko2005', 'wko2012', 'CTIF', 'ISFFR')),
   array('la', 'male', $teamDisciplines['LA']['male'],     7, array('wko2005', 'wko2012', 'CTIF', 'ISFFR')),
);

foreach ($teamScores as $value) {
  $discipline  = $value[0];
  $sex         = $value[1];
  $scores      = $value[2];
  $personCount = $value[3];
  $bestTypes   = $value[4];
  if (!count($scores)) continue;

  $title = FSS::dis2name($discipline);
  if ($sex) $title .= ' - '.FSS::sex($sex);
  echo Title::h2($title);

  $sum   = 0;
  $min = array();
  foreach ($bestTypes as $type) $min[$type] = PHP_INT_MAX;
  $max   = 0;
  $count = 0;

  foreach ($scores as $score) {
    if (FSS::isInvalid($score['time'])) continue;
    $sum += $score['time'];
    $count++;
    foreach ($bestTypes as $type) {
      if ($score['type'] == $type && $min[$type] > $score['time']) $min[$type] = $score['time'];
    }    
    if ($max < $score['time']) $max = $score['time'];
  }

  $chartTable = ChartTable::build();
  if ($count > 0) {
    foreach ($bestTypes as $type) {
      if ($min[$type] != PHP_INT_MAX) $chartTable->row('Bestzeit '.$type.':', FSS::time($min[$type]).' s');
    }
    
    $chartTable->row('Schlechteste Zeit:', FSS::time($max).' s');
    $chartTable->row('Durchschnitt:', FSS::time($sum/$count).' s');
  }
  $chartTable->row('Zeiten:', count($scores));
  $chartTable->row(Chart::img('team_scores_bad_good', array($id, $discipline.($sex?'-'.$sex:''))));

  echo Bootstrap::row()
  ->col($chartTable, 3)
  ->col(($count > 0)? Chart::img('team_scores', array($id, $discipline.($sex?'-'.$sex:''))) : '', 9);

  $countTable = CountTable::build($scores,  array("scores-".$discipline, "table-small", "group-scores"))
  ->rowAttribute('data-id', 'id')
  ->col('Datum', 'date', 5, array('class' => 'small'))
  ->col('Typ', function ($row) { return Link::event($row['event_id'], $row['event']); }, 5)
  ->col('Ort', function ($row) { return Link::place($row['place_id'], $row['place']); }, 5)
  ->col('N', function ($row) use ($id) { return FSS::teamNumber($row['team_number'], $row['competition_id'], $id, 'team'); }, 2)
  ->col('Zeit', function ($row) { return FSS::time($row['time']); }, 3);

  for ($wk = 1; $wk <= $personCount ; $wk++) {
    $countTable->col("WK".$wk, function ($row) use ($wk, $members) {
      $id = $row['person_'.$wk];
      if (!empty($id)) {
        return Link::subPerson($id, $members[$id]['name'], $members[$id]['firstname']);
      } else {
        return '';
      }
    }, 5, array('class' => 'person small', 'title' => WK::type($discipline, $sex, $wk)));
  }
  echo Bootstrap::row()->col($countTable->col('', function ($row) { return Link::competition($row['competition_id']); }, 3), 12);
}

echo Title::h2("Karte");
if (Map::isFile('teams', $id)) {
  echo Bootstrap::row()
    ->col(Map::getImg('teams', $id), 8)
    ->col('<button id="map-load" data-team-id="'.$id.'" data-team-name="'.htmlspecialchars($team['name']).'" data-lat="'.$team['lat'].'" data-lon="'.$team['lon'].'">Interaktive Karte laden</button>', 4);
} else {
  echo Bootstrap::row()
    ->col('<img src="/styling/images/no-location.png" alt=""/><br/>Keine Kartenposition vorhanden', 8)
    ->col('<button id="map-load" data-team-id="'.$id.'" data-team-name="'.htmlspecialchars($team['name']).'">Interaktive Karte zum Bearbeiten laden</button>', 4);
}
echo Bootstrap::row('hide')
->col('<div id="map-dynamic"></div>', 8)
->col('<button id="map-edit">Position bearbeiten</button><button id="map-save">Speichern</button><p id="map-edit-hint">Bitte den Marker auf die korrekte Position ziehen.</p>', 4);

echo Title::h2("Fehler melden");
echo '<p>Beim Importieren der Ergebnisse kann es immer wieder mal zu Fehlern kommen. Geraden wenn die Namen in den Ergebnislisten verkehrt geschrieben wurden, kann keine eindeutige Zuordnung stattfinden. Außerdem treten auch Probleme mit Umlauten oder anderen besonderen Buchstaben im Namen auf.</p>';
echo '<p>Ihr könnt jetzt beim Korrigieren der Daten helfen. Dafür klickt ihr auf folgenden Link und generiert eine Meldung für den Administrator. Dieser überprüft dann die Eingaben und leitet weitere Schritte ein.</p>';
echo '<p><button id="report-error" data-team-id="'.$id.'">Fehler mit diesem Team melden</button></p>';
