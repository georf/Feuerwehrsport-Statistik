<?php

$year = Check2::page()->get('id')->match('|^[1,2][0-9]{3}$|');

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hlf');
TempDB::generate('x_scores_hlm');
TempDB::generate('x_full_competitions');

echo Title::set('Überblick der Disziplinen im Jahr '.$year);

echo Bootstrap::row()
->col('<p>Die Tabellen zeigen die Leistungen der 50 besten Sportler und Mannschaften im Jahr '.$year.'. Für die Rangordnung ist nicht nur die Durchschnittszeit entscheidend. Zusätzlich werden die Strafpunkte erhöht, wenn man mehr ungültige Versuche hat und verringert, wenn man mehr Läufe absolviert hat. Somit ergibt sich ein Vergleich der konstanten Leistungen.</p>', 4)
->col('<img src="/styling/images/formel.png" alt=""/>'.'<ul>'.
      '<li>'.Link::year($year, 'Jahresübersicht').'</li>'.
      '<li>'.Link::bestScoresOfYear($year, 'Bestzeiten des Jahres').'</li>'.
      '</ul>', 5)
->col('<p>Die nebenstehende Formel zeigt die Berechnung für die Strafpunkte. Dabei sind<br/><em>g</em> = Anzahl gültiger Läufe<br/><em>u</em> = Anzahl ungültiger Läufe<br/><em>a</em> = <em>g</em> + <em>u</em><br/><em>t</em> = Zeiten der gültigen Läufe</p>', 3);
//\frac { \sum _{ i=1 }^{ n_{ gültig } }{ t_{ i } }  }{ n_{ gültig } } +15n_{ ungültig }-10n_{ gesamt }
//\frac {  \frac { 1 }{ g } \sum _{ i=1 }^{ g }{ 100t_{ i } } +15u-\sum _{ i=0 }^{ a } \frac { -i^{ 2 } }{ 23 } +10}{10}



$navTab = Bootstrap::navTab('best-of-year');
$disciplines = array(
  array('hb', false, 'female'),
  array('hb', false, 'male'),
  array('hl', false, 'female'),
  array('hl', false, 'male'),
  array('gs', true,  false),
  array('la', true,  'female'),
  array('la', true,  'male'),
);

foreach ($disciplines as $d) {
  $dis = function ($d, $year) use (&$navTab) {
    $discipline = $d[0];
    $group      = $d[1];
    $sex        = $d[2];

    if ($group) {
      $best = Statistics::calculateTeams($year, $discipline, $sex);
    } else {
      $best = Statistics::calculatePersons($year, $discipline, $sex);
    }

    $best = array_slice($best, 0, 50);

    $i = 0;
    $output = CountTable::build($best)
    ->col('Platz', function ($row) use (&$i) { $i++; return $i.'.'; }, 5)
    ->col('Name', function($row) use ($group) { return ($group)? Link::team($row['id']) : Link::fullPerson($row['id']); }, 20)
    ->col('Punkte', function($row) { return round($row['calc']/10); }, 10)
    ->col('Durchschnitt', function($row) { return FSS::time($row['avg']); }, 10)
    ->col('Zeiten', function($row) {
      $ss = array();
      foreach ($row['scores'] as $s) {
        $ss[] = Link::competition($s['competition_id'], FSS::time($s['time']), $s['event']);
      }
      return implode(', ', $ss);
    }, 70);
    $headline = FSS::dis2img($discipline).' '.strtoupper($discipline);
    if ($sex) $headline .= ' '.FSS::sex($sex);
    $navTab->tab($headline, Bootstrap::row()->col($output, 12), FSS::dis2name($discipline));
  };
  $dis($d, $year);
}

echo $navTab;