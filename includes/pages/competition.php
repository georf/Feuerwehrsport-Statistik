<?php

$competition = Check2::page()->get('id')->isIn('competitions', 'row');
$id = $competition['id'];
$competition = FSS::competition($id);

$files = $db->getRows("
  SELECT *
  FROM `file_uploads`
  WHERE `competition_id` = '".$id."'
  ORDER BY `name`
");

foreach ($files as $key => $file) {
  $files[$key]['content'] = explode(',', $file['content']);
}

$calculation = CalculationCompetition::build($competition);
$gs = $calculation->getDiscipline('gs', 'female');

$la = array();
$fs = array();
$hb = array();
$sexes = array('female', 'male');
foreach ($sexes as $sex) {
    $la[$sex] = $calculation->getDiscipline('la', $sex);
    $fs[$sex] = $calculation->getDiscipline('fs', $sex);

    $hb[$sex] = $db->getRows("
        SELECT `best`.*,
            `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
            `p`.`name` AS `name`,`p`.`firstname` AS `firstname`
        FROM (
            SELECT *
            FROM (
                (
                    SELECT `id`,`team_id`,`team_number`,
                    `person_id`,
                    `time`
                    FROM `scores`
                    WHERE `time` IS NOT NULL
                    AND `competition_id` = '".$id."'
                    AND `discipline` = 'HB'
                    AND `team_number` > -2
                ) UNION (
                    SELECT `id`,`team_id`,`team_number`,
                    `person_id`,
                    ".FSS::INVALID." AS `time`
                    FROM `scores`
                    WHERE `time` IS NULL
                    AND `competition_id` = '".$id."'
                    AND `discipline` = 'HB'
                    AND `team_number` > -2
                ) ORDER BY `time`
            ) `all`
            GROUP BY `person_id`
        ) `best`
        LEFT JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
        INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
        WHERE `sex` = '".$sex."'
        ORDER BY `time`
    ");

    $hbFinale[$sex] = $db->getRows("
        SELECT `best`.*,
            `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
            `p`.`name` AS `name`,`p`.`firstname` AS `firstname`
        FROM (
            SELECT *
            FROM (
                (
                    SELECT `id`,`team_id`,`team_number`,
                    `person_id`,
                    `time`
                    FROM `scores`
                    WHERE `time` IS NOT NULL
                    AND `competition_id` = '".$id."'
                    AND `discipline` = 'HB'
                    AND `team_number` = -2
                ) UNION (
                    SELECT `id`,`team_id`,`team_number`,
                    `person_id`,
                    ".FSS::INVALID." AS `time`
                    FROM `scores`
                    WHERE `time` IS NULL
                    AND `competition_id` = '".$id."'
                    AND `discipline` = 'HB'
                    AND `team_number` = -2
                ) ORDER BY `time`
            ) `all`
            GROUP BY `person_id`
        ) `best`
        LEFT JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
        INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
        WHERE `sex` = '".$sex."'
        ORDER BY `time`
    ");
}

$hl = $db->getRows("
    SELECT `best`.*,
        `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
        `p`.`name` AS `name`,`p`.`firstname` AS `firstname`
    FROM (
        SELECT *
        FROM (
            (
                SELECT `id`,`team_id`,`team_number`,
                `person_id`,
                `time`
                FROM `scores`
                WHERE `time` IS NOT NULL
                AND `competition_id` = '".$id."'
                AND `discipline` = 'HL'
                AND `team_number` > -2
            ) UNION (
                SELECT `id`,`team_id`,`team_number`,
                `person_id`,
                ".FSS::INVALID." AS `time`
                FROM `scores`
                WHERE `time` IS NULL
                AND `competition_id` = '".$id."'
                AND `discipline` = 'HL'
                AND `team_number` > -2
            ) ORDER BY `time`
        ) `all`
        GROUP BY `person_id`
    ) `best`
    LEFT JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
    INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
    ORDER BY `time`
");

$hlFinale = $db->getRows("
    SELECT `best`.*,
        `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
        `p`.`name` AS `name`,`p`.`firstname` AS `firstname`
    FROM (
        SELECT *
        FROM (
            (
                SELECT `id`,`team_id`,`team_number`,
                `person_id`,
                `time`
                FROM `scores`
                WHERE `time` IS NOT NULL
                AND `competition_id` = '".$id."'
                AND `discipline` = 'HL'
                AND `team_number` = -2
            ) UNION (
                SELECT `id`,`team_id`,`team_number`,
                `person_id`,
                ".FSS::INVALID." AS `time`
                FROM `scores`
                WHERE `time` IS NULL
                AND `competition_id` = '".$id."'
                AND `discipline` = 'HL'
                AND `team_number` = -2
            ) ORDER BY `time`
        ) `all`
        GROUP BY `person_id`
    ) `best`
    LEFT JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
    INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
    ORDER BY `time`
");

$zk = $db->getRows("
    SELECT
        0 AS `id`,
        `hl`.`person_id`,`p`.`name` AS `name`,`p`.`firstname` AS `firstname`,
        `hb`.`time` AS `hb`,
        `hl`.`time` AS `hl`,
        `hb`.`time` + `hl`.`time` AS `time`
    FROM (
        SELECT `person_id`,`time`
        FROM `scores`
        WHERE `time` IS NOT NULL
        AND `competition_id` = '".$id."'
        AND `discipline` = 'HL'
        AND `team_number` > -2
        ORDER BY `time`
    ) `hl`
    INNER JOIN (
        SELECT `person_id`,`time`
        FROM `scores`
        WHERE `time` IS NOT NULL
        AND `competition_id` = '".$id."'
        AND `discipline` = 'HB'
        AND `team_number` > -2
        ORDER BY `time`
    ) `hb` ON `hl`.`person_id` = `hb`.`person_id`
    INNER JOIN `persons` `p` ON `hb`.`person_id` = `p`.`id`
    GROUP BY `p`.`id`
    ORDER BY `time`
");

$dis = array(
    'hb-female' => $hb['female'],
    'hb-female-final' => $hbFinale['female'],
    'hb-male' => $hb['male'],
    'hb-male-final' => $hbFinale['male'],
    'hl' => $hl,
    'hl--final' => $hlFinale,
    'zk' => $zk,
    'gs' => $gs,
    'fs-female' => $fs['female'],
    'fs-male' => $fs['male'],
    'la-female' => $la['female'],
    'la-male' => $la['male'],
);

echo Title::set(
  htmlspecialchars($competition['event']).' - '.
  htmlspecialchars($competition['place']).' - '.
  gdate($competition['date'])
);

$toc = TableOfContents::get();
foreach ($dis as $fullKey => $scores) {
  if (count($scores)) {
    $keys = explode('-', $fullKey);
    $key = $keys[0];
    $sex = false;
    $final = false;
    if (count($keys) > 1) {
      if (!empty($keys[1])) $sex = $keys[1];
      if (count($keys) > 2) {
        $final = true;
      }
    }

    if ($final) {
      $toc->link(
        'dis-'.$fullKey,
        strtoupper($key).($sex?' '.FSS::sex($sex):'').' - Finale',
        FSS::dis2name($key).($sex?' '.FSS::sex($sex):'').' - Finale'
      );
    } else {
      $toc->link(
        'dis-'.$fullKey,
        FSS::dis2name($key).($sex?' '.FSS::sex($sex):''),
        FSS::dis2name($key).($sex?' '.FSS::sex($sex):'')
      );
      if (in_array($key, array('hb', 'hl')) && $competition['score_type']) {
        $toc->link(
          'dis-'.$fullKey.'-mannschaft',
          strtoupper($key).($sex?' '.FSS::sex($sex):'').' - Mannschaft',
          FSS::dis2name($key).($sex?' '.FSS::sex($sex):'').' - Mannschaftswertung'
        );
      }
    }
  }
}

$toc->link('toc-weblinks', 'Weblinks');
$toc->link('toc-files', 'Dateien');
$toc->link('fehler', 'Fehler oder Hinweis melden');


$overviewTable = '<table class="table table-condensed">';

if ($competition['name']) {
  $overviewTable .= '<tr><th colspan="2">Name:</th><td>'.$competition['name'].'</td></tr>';
}

$overviewTable .= '<tr><th colspan="2">Austragungsort:</th><td>'.Link::place($competition['place_id'], $competition['place']).'</td></tr>';
$overviewTable .= '<tr><th colspan="2">Typ:</th><td>'.Link::event($competition['event_id'], $competition['event']).'</td></tr>';

if ($competition['score_type']) {
    $overviewTable .= '<tr><th colspan="2">Mannschaftswertung:</th><td>'.$competition['persons'].'/'.$competition['run'].'/'.$competition['score'].'<a class="helpinfo" data-file="mannschaftswertung">&nbsp;</a></td></tr>';
} else {
    $overviewTable .= '<tr><th colspan="2">Mannschaftswertung:</th><td>Keine</td></tr>';
}

if ($competition['la']) $overviewTable .= '<tr><th colspan="2">Löschangriff:</th><td>'.FSS::laType($competition['la']).'</td></tr>';
if ($competition['fs']) $overviewTable .= '<tr><th colspan="2">4x100m:</th><td>'.FSS::fsType($competition['fs']).'</td></tr>';

$overviewTable .= '<tr><th colspan="2">Datum:</th><td>'.gdate($competition['date']).'</td></tr>';
$overviewTable .= '<tr><td colspan="3">&nbsp;</td></tr>';


$overviewTable .= '<tr><td></td><th>Frauen</th><th>Männer</th></tr>';

if (count($hb['female']) || count($hb['male']))
    $overviewTable .= '<tr title="Hindernisbahn"><th>HB:</th><td>'.count($hb['female']).'</td><td>'.count($hb['male']).'</td></tr>';

if (count($hbFinale['female']) || count($hbFinale['male']))
    $overviewTable .= '<tr title="Hindernisbahn Finale"><th>Finale:</th><td>'.count($hbFinale['female']).'</td><td>'.count($hbFinale['male']).'</td></tr>';

if (count($hl))
    $overviewTable .= '<tr title="Hakenleitersteigen"><th>HL:</th><td></td><td>'.count($hl).'</td></tr>';

if (count($hlFinale))
    $overviewTable .= '<tr title="Hakenleitersteigen Finale"><th>Finale:</th><td></td><td>'.count($hlFinale).'</td></tr>';

if (count($zk))
    $overviewTable .= '<tr title="Zweikampf"><th>ZK:</th><td></td><td>'.count($zk).'</td></tr>';

if (count($gs))
    $overviewTable .= '<tr title="Gruppenstafette"><th>GS:</th><td>'.count($gs).'</td><td></td></tr>';

if (count($fs['female']) || count($fs['male']))
    $overviewTable .= '<tr title="Feuerwehrstafette"><th>FS:</th><td>'.count($fs['female']).'</td><td>'.count($fs['male']).'</td></tr>';

if (count($la['female']) || count($la['male']))
    $overviewTable .= '<tr title="Löschangriff"><th>LA:</th><td>'.count($la['female']).'</td><td>'.count($la['male']).'</td></tr>';
$overviewTable .= '</table>';

echo Bootstrap::row()
->col($toc, 4)
->col($overviewTable, 4)
->col('<h4>Fehlversuche</h4>'.Chart::img('competition_bad_good', array($id, 'full')), 4)
->col('<form class="excel-box" method="post" action="/competition-'.$id.'.xlsx" id="form-excel">'.
  '<input type="hidden" name="competition_id" value="'.$id.'"/>'.
  '<img src="/styling/images/excel.png" alt="excel" style="float:right"/>'.
  'Daten als Excel-Datei herunterladen.'.
'</form>', 4);


foreach ($dis as $fullKey => $scores) {
  if (!count($scores)) continue;

  $keys = explode('-', $fullKey);
  $key = $keys[0];
  $sex = false;
  $final = false;
  if (count($keys) > 1) {
    if (!empty($keys[1])) $sex = $keys[1];
    if (count($keys) > 2) {
      $final = true;
    }
  }

  if (in_array($key, array('hb', 'hl', 'zk'))) {
    $sum = 0;
    $i = 0;
    $sum5 = 0;
    $i5 = 0;
    $sum10 = 0;
    $i10 = 0;
    $ave = FSS::INVALID;
    $ave5 = FSS::INVALID;
    $ave10 = FSS::INVALID;
    foreach ($scores as $score) {
      if (FSS::isInvalid($score['time'])) continue;

      $sum += $score['time'];
      $i++;
      if ($i5 < 5) {
        $sum5 += $score['time'];
        $i5++;
      }
      if ($i10 < 10) {
        $sum10 += $score['time'];
        $i10++;
      }
    }

    if ($i != 0) {
      $ave = $sum/$i;
      $ave5 = $sum5/$i5;
      $ave10 = $sum10/$i10;
    }

    echo '<h2 id="dis-'.$fullKey.'">'.FSS::dis2img($key).' '.FSS::dis2name($key).($sex?' '.FSS::sex($sex):'').($final?' - Finale':'').'</h2>';

    $chartTable = ChartTable::build()
      ->row('Bestzeit', FSS::time($scores[0]['time']))
      ->row('Wettkämpfer', count($scores))
      ->row('Durchschnitt', FSS::time($ave));

    if ($i5 == 5) $chartTable->row('Beste 5', FSS::time($ave5), 'Durchschnitt der besten Fünf');
    if ($i10 == 10) $chartTable->row('Beste 10', FSS::time($ave10), 'Durchschnitt der besten Zehn');
    if ($key != 'zk') $chartTable->row(Chart::img('competition_bad_good', array($id, $fullKey)));

    $bootstrap = Bootstrap::row()->col($chartTable, 3);
    if ($i != 0) $bootstrap->col(Chart::img('competition', array($id, $fullKey), true, 'competition_platzierung'), 9);
    echo $bootstrap;

    $countTable = CountTable::build($scores, array('single-scores', 'scores-'.$key.($final?'-final':'')))
      ->rowAttribute('data-id', 'id')
      ->col('Name', 'name', 20)
      ->col('Vorname', 'firstname', 20)
      ->col('Zeit', function ($row) { return FSS::time($row['time']); }, 10);
    if (!$final && $key != 'zk') $countTable->col('Mannschaft', function ($row) {
      return ($row['team']) ? Link::team($row['team_id'],$row['team']) : '';
    }, 40, array('class' => 'team'));
    if (!$final && $key != 'zk' && $competition['score_type']) $countTable->col('W', function ($row) {
      return FSS::teamNumber($row['team_number']);
    }, 8, array('class' => 'number'));
    if ($key == 'zk') $countTable
      ->col('HB', function ($row) { return FSS::time($row['hb']); }, 10)
      ->col('HL', function ($row) { return FSS::time($row['hl']); }, 10);
    $countTable->col('', function ($row) { return Link::person($row['person_id'], 'Details', $row['firstname'], $row['name']); }, 12);
    echo Bootstrap::row()->col($countTable, 12);


    // Mannschaftswertung
    if (!$final && $key != 'zk' && $competition['score_type']) {
      echo '<h2 id="dis-'.$fullKey.'-mannschaft">'.FSS::dis2img($key).' ',FSS::dis2name($key);
      if ($sex) echo ' '.FSS::sex($sex);
      echo ' - Mannschaftswertung</h2>';

      // Bereche die Wertung
      $teams = array();
      foreach ($scores as $score) {
        if ($score['team_number'] < 0) continue;
        if (!$score['team_id']) continue;

        $uniqTeam = $score['team_id'].$score['team_number'];
        if (!isset($teams[$uniqTeam])) {
          $teams[$uniqTeam] = array(
            'name' => $score['team'],
            'short' => $score['shortteam'],
            'id' => $score['team_id'],
            'number' => $score['team_number'],
            'scores' => array(),
          );
        }

        $teams[$uniqTeam]['scores'][] = $score;
      }

      // sort every persons in teams
      foreach ($teams as $uniqTeam => $team) {
        $time = 0;

        usort($team['scores'], function($a, $b) {
          if ($a['time'] == $b['time']) return 0;
          elseif ($a['time'] > $b['time']) return 1;
          else return -1;
        });

        if (count($team['scores']) < $competition['score']) {
          $teams[$uniqTeam]['time'] = FSS::INVALID;
          continue;
        }

        for($i = 0; $i < $competition['score']; $i++) {
          if ($team['scores'][$i]['time'] == FSS::INVALID) {
            $teams[$uniqTeam]['time'] = FSS::INVALID;
            continue 2;
          }
          $time += $team['scores'][$i]['time'];
        }
        $teams[$uniqTeam]['time'] = $time;
      }

      // Sortiere Teams nach Zeit
      uasort($teams, function ($a, $b) {
        if ($a['time'] == $b['time']) return 0;
        elseif ($a['time'] > $b['time']) return 1;
        else return -1;
      });

      echo '<table class="table">';

      foreach ($teams as $uniqTeam => $team) {
        echo '<tr>';
        echo '<td>'.Link::team($team['id'], $team['short']).'</td>';
        echo '<td>'.FSS::time($team['time']).'</td>';

        $inScore = array();
        $outScore = array();
        $i = 0;
        foreach ($team['scores'] as $score) {
          $link = Link::person($score['person_id'], 'sub', $score['name'], $score['firstname'], FSS::time($score['time']));
          if ($i < $competition['score']) $inScore[] = $link;
          else $outScore[] = $link;
          $i++;
        }

        echo '<td style="font-size:0.9em">'.implode(', ', $inScore).'</td>';
        echo '<td style="font-size:0.9em">'.implode(', ', $outScore).'</td>';
        echo '<td';
        if (count($team['scores']) > $competition['run']) echo ' style="background:FF0000"';
        echo '>'.count($team['scores']).' von '.$competition['run'].'</td>';
        echo '</tr>';
      }
      echo '</table>';
    }
  } else {
    echo '<h2 id="dis-'.$fullKey.'">'.FSS::dis2img($key).' ',FSS::dis2name($key);
    if ($sex) echo ' '.FSS::sex($sex);
    echo '</h2>';

    $sum = 0;
    $i = 0;
    foreach ($scores as $score) {
      if (FSS::isInvalid($score['time'])) continue;
      $sum += $score['time'];
      $i++;
    }
    $ave = ($i > 0) ? $sum/$i : FSS::INVALID;

    echo Bootstrap::row()
    ->col(ChartTable::build()
      ->row('Bestzeit', FSS::time($scores[0]['time']))
      ->row('Mannschaften', count($scores))
      ->row('Durchschnitt', FSS::time($ave))
      ->row(Chart::img('competition_bad_good', array($id, $fullKey))), 3)
    ->col(($i > 0) ? Chart::img('competition', array($id, $fullKey)) : "", 9);

    $countTable = CountTable::build($scores, array('group-scores', 'scores-'.$key))
    ->rowAttribute('data-id', 'id')
    ->col('Team', function ($row) use ($id) {
      $run = (array_key_exists('run', $row)) ? ' '.$row['run'] : '';
      return Link::team($row['team_id'], $row['shortteam'].' '.FSS::teamNumber($row['team_number'], $id, $row['team_id'], 'competition').$run, 'Details zu '.$row['team'].' anzeigen');
    }, 25)
    ->col('Zeit', function ($row) { return FSS::time($row['time']); }, 10);

    for ($wk = 1; $wk < 8; $wk++) {
      if (array_key_exists('person_'.$wk, $scores[0])) {
        //'title="'.WK::type($wk, $sex, $key).'
        $countTable->col('WK'.$wk, function ($row) use ($wk) {
          return (!empty($row['person_'.$wk])) ? Link::person($row['person_'.$wk], 'sub', $row['name'.$wk], $row['firstname'.$wk]) : '';
        }, 25, array('class' => 'person'));
      }
    }
    echo Bootstrap::row()->col($countTable, 12);
  }

  $current_files = array();
  $fkey = $key;
  if ($sex && $sex == 'female') $fkey .= 'w';
  if ($sex && $sex == 'male') $fkey .= 'm';

  foreach ($files as $file) {
    if (in_array($fkey, $file['content'])) {
      $current_files[] = $file;
    }
  }

  if (count($current_files)) {
    $lis = array();
    foreach ($current_files as $file) {
      $lis[] = '<li><a href="/files/'.$id.'/'.$file['name'].'">'.$file['name'].'</a></li>';
    }
    echo Bootstrap::row()->col('', 7)->col(
      '<h5>'.FSS::dis2img($key).' Verknüpfte Ergebnisse</h5>'.
      '<ul class="disc">'.
        implode($lis).
      '</ul>'
    , 5);
  }
}


$links = $db->getRows("
  SELECT *
  FROM `links`
  WHERE `for_id` = '".$id."'
  AND `for` = 'competition'
");

echo Title::h2('Weblinks zu diesem Wettkampf', 'toc-weblinks');
if (count($links)) {
  echo '<ul>';
  foreach ($links as $link) {
    echo '<li>',Link::a($link['url'], $link['name']),'</li>';
  }
  echo '</ul>';
}
echo '<button id="add-link" data-for-id="'.$id.'" data-for-table="competition">Link hinzufügen</button>';

echo Title::h2('Dateien zu diesem Wettkampf', 'toc-files');
if (count($files)) {
  $c_types = array(
    'hl'  =>  'HL',
    'hbm' =>  'HB m',
    'hbw' =>  'HB w',
    'gs'  =>  'GS',
    'law' =>  'LA w',
    'lam' =>  'LA m',
    'fsw' =>  'FS w',
    'fsm' =>  'FS m'
  );

  echo '<table class="table table-condensed"><tr><th>Dateien</th><th>Enthaltene Ergebnisse</th></tr>';
  foreach ($files as $file) {
    echo '<tr><td><a href="/files/',$id,'/',$file['name'],'">',$file['name'],'</a></td><td>';

    $current_types = array();
    foreach ($c_types as $t => $n) {
      if (in_array($t, $file['content'])) {
        $current_types[] = '<span>'.$n.'</span>';
      }
    }
    echo implode(', ', $current_types);
    echo '</td></tr>';
  }
  echo '</table>';
}

echo '<button id="add-file">Datei hinzufügen</button>';

echo '<div id="add-file-form" style="display:none;">
  <form action="/page/competition_upload.html" method="post" enctype="multipart/form-data">
    <h3>Es dürfen nur PDFs hochgeladen werden.</h3>
    <table class="table">
      <tr><th rowspan="2">Datei</th><th colspan="8">Folgende Ergebnisse sind in dieser Datei enthalten</th></tr>
      <tr><th>HL</th><th>HB w</th><th>HB m</th><th>GS</th><th>LA w</th><th>LA m</th><th>FS w</th><th>FS m</th></tr>
      <tr class="input-file-row"><td><input type="file" name="result_0" /></td>
        <td title="Hakenleitersteigen"><input type="checkbox" name="hl_0" value="true"/></td>
        <td title="Hindernisbahn weiblich"><input type="checkbox" name="hbw_0" value="true"/></td>
        <td title="Hindernisbahn männlich"><input type="checkbox" name="hbm_0" value="true"/></td>
        <td title="Gruppenstafette"><input type="checkbox" name="gs_0" value="true"/></td>
        <td title="Löschangriff weiblich"><input type="checkbox" name="law_0" value="true"/></td>
        <td title="Löschangriff männlich"><input type="checkbox" name="lam_0" value="true"/></td>
        <td title="Feuerwehrstafette weiblich"><input type="checkbox" name="fsw_0" value="true"/></td>
        <td title="Feuerwehrstafette männlich"><input type="checkbox" name="fsm_0" value="true"/></td>
      </tr>
    </table>
    <p>
      <input type="hidden" name="id" value="'.$id.'"/>
      <a id="more-files" href="">Noch eine Datei auswählen</a> <br/>
      <button>Hochladen</button>
    </p>
  </form>
</div>
';

$missedItems = array();
foreach ($calculation->missed() as $key => $missed) {
  if ($missed == 1) $missedItems[] = $calculation->missedItem[$key];
}
$missedCol = "";
if (count($missedItems)) {
  $missedCol .= "<h4>Folgende Informationen fehlen:</h4>";
  $missedCol .= "<ul><li>".implode("</li><li>", $missedItems)."</li></ul>";
}
$no_cache[] = 'competition';
echo Title::h2('Fehler oder Hinweis melden', 'fehler');
echo '<p>Beim Importieren der Ergebnisse kann es immer wieder mal zu Fehlern kommen. Geraden wenn die Namen in den Ergebnislisten verkehrt geschrieben wurden, kann keine eindeutige Zuordnung stattfinden. Außerdem treten auch Probleme mit Umlauten oder anderen besonderen Buchstaben im Namen auf.</p>';
echo '<p>Ihr könnt jetzt beim Korrigieren der Daten helfen. Dafür klickt ihr auf folgenden Link und generiert eine Meldung für den Administrator. Dieser überprüft dann die Eingaben und leitet weitere Schritte ein.</p>';
echo Bootstrap::row()
->col(
  '<p>Auch Hinweise können zu einem Wettkampf gegeben werden. Dazu zählen zum Beispiel:</p>'.
  '<ul>'.
    '<li>Name des Wettkampfs</li>'.
    '<li>Besondere Bindungen</li>'.
    '<li>Wetter</li>'.
    '<li>Aufteilung auf mehrere Orte oder Tage</li>'.
  '</ul>'.
  '<p><button id="report-error" data-competition-id="'.$id.'" data-competition-name="'.$competition['name'].'">Fehler oder Hinweis melden</button></p>', 8)
->col($missedCol, 4, array("missed-".count($missedItems)));

