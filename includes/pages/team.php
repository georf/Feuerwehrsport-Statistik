<?php
$team = Check2::page()->get('id')->isIn('teams', 'row');
$id = $team['id'];


TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hlf');
TempDB::generate('x_scores_hlm');
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
  'HL' => 0
);

$scores = $db->getRows("
  SELECT `discipline`,`person_id`,`count`
  FROM (
    SELECT `discipline`, `person_id`, COUNT(`key`) AS `count`
    FROM (
      SELECT `gst`.`discipline`,`pp`.`person_id`, CONCAT(`person_id`, `discipline`) AS `key`
      FROM `group_scores` `gs`
      INNER JOIN `person_participations` `pp` ON `pp`.`score_id` = `gs`.`id`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `gs`.`team_id` = '".$team['id']."'
    ) `group_disciplines`
    GROUP BY `key`
  UNION ALL
    SELECT `discipline`,`person_id`,COUNT(`id`) AS `count`
    FROM `scores`
    WHERE `team_id` = '".$team['id']."'
    AND `discipline` = 'HB'
    GROUP BY `person_id`
  UNION ALL
    SELECT `discipline`,`person_id`,COUNT(`id`) AS `count`
    FROM `scores`
    WHERE `team_id` = '".$team['id']."'
    AND `discipline` = 'HL'
    GROUP BY `person_id`
  ) `i`
");

foreach ($scores as $score) {
  $pid = $score['person_id'];
  if (!isset($members[$pid])) $members[$pid] = $member;
  $members[$pid][$score['discipline']] += $score['count'];
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

$calculation = CalculationTeam::build($team);

// Mannschaftswertung
$team_scores = array(
  'hb-female' => array(),
  'hb-male' => array(),
  'hl-female' => array(),
  'hl-male' => array(),
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
  'x_scores_hlf' => 'hl-female',
  'x_scores_hlm' => 'hl-male',
);
foreach ($competitions as $competition) {
  foreach ($single_disciplines as $table => $discipline) {
    $scores = $db->getRows("
      SELECT `best`.*,
        `p`.`firstname`, `p`.`name`
      FROM (
        SELECT *
        FROM (
          SELECT `id`,`team_number`,
          `person_id`,
          COALESCE(`time`, ".FSS::INVALID.") AS `time`
          FROM `".$table."`
          WHERE `competition_id` = '".$competition['id']."'
          AND `team_number` >= 0
          AND `team_id` = '".$id."'
          ORDER BY `time`
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
  UNION ALL
    SELECT `competition_id`,0 AS `single`,COUNT(*) AS `gs`,0 AS `la`,0 AS `fs`
    FROM `group_scores` `gs`
    INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
    INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
    WHERE `team_id` = '".$id."'
    AND `gst`.`discipline` = 'GS'
    GROUP BY `competition_id`
  UNION ALL
    SELECT `competition_id`,0 AS `single`,0 AS `gs`,COUNT(*) AS `la`,0 AS `fs`
    FROM `group_scores` `gs`
    INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
    INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
    WHERE `team_id` = '".$id."'
    AND `gst`.`discipline` = 'LA'
    GROUP BY `competition_id`
  UNION ALL
    SELECT `competition_id`,0 AS `single`,0 AS `gs`,0 AS `la`,COUNT(*) AS `fs`
    FROM `group_scores` `gs`
    INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
    INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
    WHERE `team_id` = '".$id."'
    AND `gst`.`discipline` = 'FS'
    GROUP BY `competition_id`
  ) `i`
  INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `i`.`competition_id`
  GROUP BY `competition_id`
");

$dcups = $db->getRows("
  SELECT `year`, `ready`, `dcup_id`
  FROM `dcups` `d`
  INNER JOIN `scores_dcup_team` `s` ON `s`.`dcup_id` = `d`.`id`
  WHERE `team_id` = '".$id."'
  GROUP BY `dcup_id`
  ORDER BY `year` DESC
");
foreach ($dcups as $i => $dcup) {
  $dcups[$i]['scores'] = array();
  foreach (FSS::$sexes as $sex) {
    list($teams, $competitions_dcup) = DcupCalculation::getTeamScores($sex, $dcup['dcup_id']);
    for ($z = 0; $z < count($teams); $z++) {
      if ($teams[$z]->id == $id) {
        $dcups[$i]['scores'][] = array($sex, $teams[$z], $z + 1);
      }
    }
  }
}

$toc = TableOfContents::get();
if (count($members)) $toc->link('wettkaempfer', 'Wettkämpfer');
$toc->link('wettkaempfe', 'Wettkämpfe');
foreach ($team_scores as $fullKey => $tscores) {
  if (!count($tscores)) continue;
  $keys = explode('-', $fullKey);
  $name = $title = FSS::dis2name($keys[0]);
  if (count($keys) > 1) {
    $name  .= ' '.FSS::sexSymbol($keys[1]);
    $title .= ' '.FSS::sex($keys[1]);
  }
  $toc->link($fullKey, $name, $title.' - Mannschaftswertung');
}

foreach ($calculation->disciplineTypes as $discipline => $sexes) {
  foreach ($sexes as $sex => $types) {
    if (!count($types)) continue;
    $name   = $title = FSS::dis2name($discipline);
    $key    = $discipline;
    $name  .= ' '.FSS::sexSymbol($sex);
    $title .= ' '.FSS::sex($sex);
    $key   .= '-'.$sex;
    $toc->link($key, $name, $title);
  }
}

if (count($dcups)) $toc->link('dcup', 'D-Cup-Wertungen');
$toc->link('karte', 'Karte');
$toc->link('fehler', 'Fehler melden');

$logoCol = TeamLogo::getTall(
  $team['logo'], 
  $team['short'], 
  '<div id="logo-upload" data-team-id="'.$id.'"><span class="label label-default">Logo hochladen</span></div>');

if (count($members) > 0) {
  $logoCol .= "<hr/>".Chart::img('team_sex', array($id));
}

echo Bootstrap::row()
->col($logoCol, 2)
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
  , 7)
->col($toc, 3);

if (count($members)) {
  echo Title::h2('Wettkämpfer', 'wettkaempfer');
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
}

echo Title::h2('Wettkämpfe', 'wettkaempfe');
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
  echo Title::h2($title, $fullKey);

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

foreach ($calculation->disciplineTypes as $discipline => $sexes) {
  foreach ($sexes as $sex => $types) {
    if (!count($types)) continue;
    
    $title = FSS::dis2name($discipline);
    $key = $discipline;
    $title .= ' - '.FSS::sex($sex);
    $key   .= '-'.$sex;
    echo Title::h2($title, $key);

    foreach ($types as $type) {
      echo Title::h3($type['name']);
      $scores = $calculation->getGroupScores($type, $sex);

      $sum   = 0;
      $min   = PHP_INT_MAX;
      $max   = 0;
      $count = 0;

      foreach ($scores as $score) {
        if (FSS::isInvalid($score['time'])) continue;
        $sum += $score['time'];
        $count++;
        $min = min($min, $score['time']);
        $max = max($max, $score['time']);
      }

      $chartTable = ChartTable::build();
      if ($count > 0) {
        $chartTable->row('Bestzeit:', FSS::time($min).' s');
        $chartTable->row('Schlechteste Zeit:', FSS::time($max).' s');
        $chartTable->row('Durchschnitt:', FSS::time($sum/$count).' s');
      }
      $chartTable->row('Zeiten:', count($scores));
      $chartTable->row(Chart::img('team_scores_bad_good', array($id, $type['id'], $sex)));

      echo Bootstrap::row()
      ->col($chartTable, 3)
      ->col(($count > 0)? Chart::img('team_scores', array($id, $type['id'], $sex)) : '', 9);

      $countTable = CountTable::build($scores,  array("scores-".$discipline, "table-small", "group-scores"))
      ->rowAttribute('data-id', 'id')
      ->col('Datum', 'date', 5, array('class' => 'small'))
      ->col('Typ', function ($row) { return Link::event($row['event_id'], $row['event']); }, 5)
      ->col('Ort', function ($row) { return Link::place($row['place_id'], $row['place']); }, 5)
      ->col('N', function ($row) { return FSS::teamNumber($row['team_number']); }, 2)
      ->col('Zeit', function ($row) { return FSS::time($row['time']); }, 3);

      for ($wk = 1; $wk <= WK::count($discipline); $wk++) {
        $countTable->col("WK".$wk, function ($row) use ($wk, $members) {
          $id = $row['person_'.$wk];
          if (!empty($id)) {
            return Link::subPerson($id, $members[$id]['name'], $members[$id]['firstname']);
          } else {
            return '';
          }
        }, 5, array('class' => 'person small', 'title' => WK::type($wk, $sex, $discipline)));
      }
      echo Bootstrap::row()->col($countTable->col('', function ($row) { return Link::competition($row['competition_id']); }, 3), 12);
  }}
}

if (count($dcups)) {
  echo Title::h2("D-Cup-Wertungen", "dcup");
  echo '<table class="table table-condensed">';
  echo '<tr><th>Jahr</th><th>Geschlecht</th><th>Wettkämpfe</th><th>Punkte</th><th>Platz</th></tr>';
  foreach($dcups as $dcup) {
    echo '<tr>';
    echo '<th rowspan="'.count($dcup['scores']).'">'.$dcup['year'].'</th>';
    for ($i = 0; $i < count($dcup['scores']); $i++) {
      list($sex, $dcupTeam, $position) = $dcup['scores'][$i];
      if ($i != 0) echo '<tr>';
      echo '<td>'.FSS::sex($sex).' ('.($dcupTeam->number+1).')</td>';
      echo '<td>'.implode(", ", $dcupTeam->getCompetitionLinks()).'</td>';
      echo '<td>'.$dcupTeam->getSum().'</td>';
      echo '<td>'.$position.'.</td>';
      echo '</tr>';
    }
  }
  echo '</table>';
}

echo Title::h2("Karte", "karte");
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

// if (Map::isFile('teams', $id)) {
//   $rows = $db->getRows("
//     SELECT 111.045* DEGREES(ACOS(COS(RADIANS(lat))
//                    * COS(RADIANS(".str_replace(',', '.', $team["lat"])."))
//                    * COS(RADIANS(lon) - RADIANS(".str_replace(',', '.', $team["lon"])."))
//                    + SIN(RADIANS(lat))
//                    * SIN(RADIANS(".str_replace(',', '.', $team["lat"]).")))) AS `distance`, 
//       `name`, `id`
//     FROM `places`
//     WHERE `lat` IS NOT NULL AND `lat` != ''
//     ORDER BY distance
//   ");
//   print_r($rows);
// }

echo Title::h2("Fehler melden", "fehler");
echo '<p>Beim Importieren der Ergebnisse kann es immer wieder mal zu Fehlern kommen. Geraden wenn die Namen in den Ergebnislisten verkehrt geschrieben wurden, kann keine eindeutige Zuordnung stattfinden. Außerdem treten auch Probleme mit Umlauten oder anderen besonderen Buchstaben im Namen auf.</p>';
echo '<p>Ihr könnt jetzt beim Korrigieren der Daten helfen. Dafür klickt ihr auf folgenden Link und generiert eine Meldung für den Administrator. Dieser überprüft dann die Eingaben und leitet weitere Schritte ein.</p>';
echo '<p><button id="report-error" data-team-id="'.$id.'">Fehler mit diesem Team melden</button></p>';
