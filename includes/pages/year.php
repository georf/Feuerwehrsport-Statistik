<?php

$year = Check2::page()->get('id')->match('|^[1,2][0-9]{3}$|');

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hlf');
TempDB::generate('x_scores_hlm');
TempDB::generate('x_scores_hl');
TempDB::generate('x_full_competitions');

$dcup1 = '';
$dcup2 = '';
if ($db->getFirstRow("SELECT * FROM `dcups` WHERE `year` = '".$year."' LIMIT 1")) {
  $hlw = $year > 2014;
  $dcup1 = '<li>'.Link::dcup($year).'</li>';
  $dcup2 = '<p>Außerdem sind für das Jahr '.$year.' Einzelergebnisse für die D-Cup-Wertung vorhanden:</p><ul>'.
  '<li>'.FSS::dis2img('hb').' '.Link::dcup_single($year, 'hb', 'female', false, 'Hindernisbahn weiblich').'</li>'.
  '<li>'.FSS::dis2img('hb').' '.Link::dcup_single($year, 'hb', 'male', false, 'Hindernisbahn männlich').'</li>'.
  ($hlw ? '<li>'.FSS::dis2img('hl').' '.Link::dcup_single($year, 'hl', 'female', false, 'Hakenleitersteigen weiblich').'</li>' : '').
  '<li>'.FSS::dis2img('hl').' '.Link::dcup_single($year, 'hl', 'male', false, 'Hakenleitersteigen männlich').'</li>'.
  ($hlw ? '<li>'.FSS::dis2img('zk').' '.Link::dcup_single($year, 'zk', 'female', false, 'Zweikampf weiblich').'</li>' : '').
  '<li>'.FSS::dis2img('zk').' '.Link::dcup_single($year, 'zk', 'male', false, 'Zweikampf männlich').'</li>'.
  '</ul>';
}

echo Title::set('Überblick für Jahr '.$year);
echo Bootstrap::row()
->col('<ul>'.
      '<li>'.Link::bestScoresOfYear($year, 'Bestzeiten des Jahres').'</li>'.
      '<li>'.Link::bestPerformanceOfYear($year, 'Bestleistungen des Jahres').'</li>'.
      $dcup1.
      '</ul>', 5)
->col('<p>Diese Seite zeigt Statistiken über das Jahr '.
      $year.'. Für Einzeldisziplinen sind die Unterseiten '.
      Link::bestScoresOfYear($year, 'Bestzeiten des Jahres').
      ' und '.Link::bestPerformanceOfYear($year, 'Bestleistungen des Jahres').
      ' interessant.</p>'.$dcup2, 7);

echo Title::h2('Auswertung');
echo Analysis::generalCharts('year', $year);
echo Title::h2('Disziplinen');
echo Analysis::bestOfYears('year', $year);
