<?php

$year = Check2::page()->get('id')->match('|^[1,2][0-9]{3}$|');

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');
TempDB::generate('x_full_competitions');

echo Title::set('Überblick für Jahr '.$year);
echo Bootstrap::row()
->col('<ul>'.
      '<li>'.Link::bestScoresOfYear($year, 'Bestzeiten des Jahres').'</li>'.
      '<li>'.Link::bestPerformanceOfYear($year, 'Bestleistungen des Jahres').'</li>'.
      '</ul>', 5)
->col('<p>Diese Seite zeigt Statistiken über das Jahr '.
      $year.'. Für Einzeldisziplinen sind die Unterseiten '.
      Link::bestScoresOfYear($year, 'Bestzeiten des Jahres').
      ' und '.Link::bestPerformanceOfYear($year, 'Bestleistungen des Jahres').
      ' interessant.</p>', 7);

echo Title::h2('Auswertung');
echo Analysis::generalCharts('year', $year);
echo Title::h2('Disziplinen');
echo Analysis::bestOfYears('year', $year);
