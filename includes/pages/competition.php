<?php

$id = Check2::page()->get('id')->isIn('competitions');

$competition = FSS::competition($id);
$calculation = CalculationCompetition::build($competition);
$disciplines = $calculation->disciplines();

echo Title::set(
  htmlspecialchars($competition['event']).' - '.
  htmlspecialchars($competition['place']).' - '.
  gdate($competition['date'])
);

$toc = TableOfContents::get();
foreach ($disciplines as $discipline) {
  if (!$calculation->count($discipline)) continue;
  $toc->link(
    'dis-'.$discipline['fullKey'],
    $calculation->disciplineName($discipline, true, false),
    $calculation->disciplineName($discipline, false, false)
  );
  
  if (!$discipline['final'] && in_array($discipline['key'], array('hb', 'hl')) && $competition['score_type']) {
    $toc->link(
      'dis-'.$discipline['fullKey'],
      $calculation->disciplineName($discipline, true, true),
      $calculation->disciplineName($discipline, false, true)
    );
  }
}
$toc->link('toc-weblinks', 'Weblinks');
$toc->link('toc-files', 'Dateien');
$toc->link('fehler', 'Fehler oder Hinweis melden');

$overviewTable = '<table class="table table-condensed">';
if ($competition['name'])
  $overviewTable .= '<tr><th colspan="2">Name:</th><td>'.$competition['name'].'</td></tr>';
$overviewTable .= '<tr><th colspan="2">Austragungsort:</th><td>'.Link::place($competition['place_id'], $competition['place']).'</td></tr>';
$overviewTable .= '<tr><th colspan="2">Typ:</th><td>'.Link::event($competition['event_id'], $competition['event']).'</td></tr>';

if ($calculation->countSingleScores()) {
  if ($competition['score_type']) {
    $overviewTable .= '<tr><th colspan="2">Mannschaftswertung:</th><td>'.$competition['persons'].'/'.$competition['run'].'/'.$competition['score'].'</td></tr>';
  } else {
    $overviewTable .= '<tr><th colspan="2">Mannschaftswertung:</th><td>Keine</td></tr>';
  }
}
if ($competition['la'])
  $overviewTable .= '<tr><th colspan="2">Löschangriff:</th><td>'.FSS::laType($competition['la']).'</td></tr>';
if ($competition['fs'])
  $overviewTable .= '<tr><th colspan="2">4x100m:</th><td>'.FSS::fsType($competition['fs']).'</td></tr>';
$overviewTable .= '<tr><th colspan="2">Datum:</th><td>'.gdate($competition['date']).'</td></tr>';
$overviewTable .= '<tr><td colspan="3">&nbsp;</td></tr>';
$overviewTable .= '<tr><td></td><th>Frauen</th><th>Männer</th></tr>';
if ($calculation->count('hb', 'female') || $calculation->count('hb', 'male'))
  $overviewTable .= '<tr title="Hindernisbahn"><th>'.FSS::dis2img('hb').' HB:</th><td>'.$calculation->count('hb', 'female').'</td><td>'.$calculation->count('hb', 'male').'</td></tr>';
if ($calculation->count('hb', 'female', true) || $calculation->count('hb', 'male', true))
  $overviewTable .= '<tr title="Hindernisbahn Finale"><th>'.FSS::dis2img('hb').' Finale:</th><td>'.$calculation->count('hb', 'male', true).'</td><td>'.$calculation->count('hb', 'male', true).'</td></tr>';
if ($calculation->count('hl'))
  $overviewTable .= '<tr title="Hakenleitersteigen"><th>'.FSS::dis2img('hl').' HL:</th><td></td><td>'.$calculation->count('hl').'</td></tr>';
if ($calculation->count('hb', null, true))
  $overviewTable .= '<tr title="Hakenleitersteigen Finale"><th>'.FSS::dis2img('hl').' Finale:</th><td></td><td>'.$calculation->count('hl', null, true).'</td></tr>';
if ($calculation->count('zk'))
  $overviewTable .= '<tr title="Zweikampf"><th>'.FSS::dis2img('zk').' ZK:</th><td></td><td>'.$calculation->count('zk').'</td></tr>';
if ($calculation->count('gs'))
  $overviewTable .= '<tr title="Gruppenstafette"><th>'.FSS::dis2img('gs').' GS:</th><td>'.$calculation->count('gs').'</td><td></td></tr>';
if ($calculation->count('fs', 'female') || $calculation->count('fs', 'male'))
  $overviewTable .= '<tr title="Feuerwehrstafette"><th>'.FSS::dis2img('fs').' FS:</th><td>'.$calculation->count('fs', 'female').'</td><td>'.$calculation->count('fs', 'male').'</td></tr>';
if ($calculation->count('la', 'female') || $calculation->count('la', 'male'))
  $overviewTable .= '<tr title="Löschangriff"><th>'.FSS::dis2img('la').' LA:</th><td>'.$calculation->count('la', 'female').'</td><td>'.$calculation->count('la', 'male').'</td></tr>';
$overviewTable .= '</table>';

echo Bootstrap::row()
->col($toc, 4)
->col($overviewTable, 4)
->col('<h4>Fehlversuche</h4>'.Chart::img('competition_bad_good', array($id, 'full')), 4);

foreach ($disciplines as $discipline) {
  if (!$calculation->count($discipline)) continue;

  $scores = $calculation->scores($discipline);
  $fullKey = $discipline['fullKey'];
  $key = $discipline['key'];
  $final = $discipline['final'];
  $sex = $discipline['sex'];

  if (in_array($discipline['key'], array('hb', 'hl', 'zk'))) {
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

    echo '<h2 id="dis-'.$fullKey.'">'.FSS::dis2img($key).' '.$calculation->disciplineName($discipline).'</h2>';

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

    if (!$final && $key != 'zk' && $competition['score_type']) {
      echo '<h2 id="dis-'.$fullKey.'-mannschaft">'.FSS::dis2img($key).' '.$calculation->disciplineName($discipline, false, true).'</h2>';

      $teams = TeamScore::build($scores, $competition['score'])->sorted();

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
    echo '<h2 id="dis-'.$fullKey.'">'.FSS::dis2img($key).' '.$calculation->disciplineName($discipline).'</h2>';

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

  $lis = array();
  foreach ($calculation->getResultFiles(FSS::buildSexKey($key, $sex)) as $file) {
    $lis[] = '<li><a href="/files/'.$id.'/'.$file->name.'">'.$file->name.'</a></li>';
  }
  if (count($lis)) {
    echo Bootstrap::row()->col('', 7)->col(
      '<h5>'.FSS::dis2img($key).' Verknüpfte Ergebnisse</h5>'.
      '<ul class="disc">'.
        implode($lis).
      '</ul>'
    , 5);
  }
}


$links = Link::databaseLinksFor('competition', $id);
echo Title::h2('Weblinks zu diesem Wettkampf', 'toc-weblinks');
if (count($links)) {
  echo '<ul><li>'.implode('</li><li>', $links).'</li></ul>';
}
echo '<button id="add-link" data-for-id="'.$id.'" data-for-table="competition">Link hinzufügen</button>';

echo Title::h2('Dateien zu diesem Wettkampf', 'toc-files');
if (count($calculation->getResultFiles())) {
  echo '<table class="table table-condensed"><tr><th>Dateien</th><th>Enthaltene Ergebnisse</th></tr>';
  foreach ($calculation->getResultFiles() as $file) {
    echo '<tr><td><a href="/files/',$id,'/',$file->name,'">',$file->name,'</a></td><td>';
    $currentTypes = array();
    foreach ($calculation->disciplines() as $discipline) {
      if ($discipline['final'] || !$file->hasKey($discipline['sexKey'])) continue;
      $currentTypes[] = 
        '<span title="'.$calculation->disciplineName($discipline).'">'.strtoupper($discipline['key']).($discipline['sex']?' '.FSS::sexSymbol($discipline['sex']):'').'</span>';
    }
    echo implode(', ', $currentTypes);
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
      <tr>';
foreach ($calculation->disciplines() as $discipline) {
  if ($discipline['final']) continue;
  echo '<th title="'.$calculation->disciplineName($discipline).'">'.strtoupper($discipline['key']).($discipline['sex']?' '.FSS::sexSymbol($discipline['sex']):'').'</th>';
}
echo '</tr><tr class="input-file-row"><td><input type="file" name="result_0" /></td>';
foreach ($calculation->disciplines() as $discipline) {
  if ($discipline['final']) continue;
  echo '<td title="'.$calculation->disciplineName($discipline).'"><input type="checkbox" name="'.$discipline['sexKey'].'_0" value="true"/></td>';
}
echo '</tr>
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

echo Title::h2('Fehler oder Hinweis melden', 'fehler');
echo '<p>Beim Importieren der Ergebnisse kann es immer wieder mal zu Fehlern kommen. Geraden wenn die Namen in den Ergebnislisten verkehrt geschrieben wurden, kann keine eindeutige Zuordnung stattfinden. Außerdem treten auch Probleme mit Umlauten oder anderen besonderen Buchstaben im Namen auf.</p>';
echo '<p>Ihr könnt jetzt beim Korrigieren der Daten helfen. Dafür klickt ihr auf folgenden Link und generiert eine Meldung für den Administrator. Dieser überprüft dann die Eingaben und leitet weitere Schritte ein.</p>';
echo Bootstrap::row()
->col(
  '<p>Auch Hinweise können zu einem Wettkampf gegeben werden. Dazu zählen zum Beispiel:</p>'.
  '<ul>'.
    '<li>Name des Wettkampfs</li>'.
    '<li>Besondere Bedindungen</li>'.
    '<li>Wetter</li>'.
    '<li>Aufteilung auf mehrere Orte oder Tage</li>'.
  '</ul>'.
  '<p><button id="report-error" data-competition-id="'.$id.'" data-competition-name="'.$competition['name'].'">Fehler oder Hinweis melden</button></p>', 8)
->col($missedCol, 4, array("missed-".count($missedItems)));

