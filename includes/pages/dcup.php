<?php

TempDB::generate('x_full_competitions');

$year = Check2::page()->get('id')->match('|^[1,2][0-9]{3}$|');
$dcup = $db->getFirstRow("SELECT * FROM `dcups` WHERE `year` = '".$year."' LIMIT 1");
Check2::page()->isTrue($dcup);
$id = $dcup['id'];

echo Bootstrap::row()
->col('<img style="height:100px;" src="/styling/images/dfv.png"/>', 2)
->col(Title::set('D-Cup Gesamtwertung - '.$year), 10);

echo DcupCalculation::notReadyBox($dcup);

$under = '';
if ($dcup['u']) {
  $under =
  '<h4>'.$dcup['u'].'</h4>'.
  '<ul>'.
  '<li>'.FSS::dis2img('hb').' '.Link::dcup_single($year, 'hbfu', 'Hindernisbahn weiblich - '.$dcup['u']).'</li>'.
  '<li>'.FSS::dis2img('hb').' '.Link::dcup_single($year, 'hbmu', 'Hindernisbahn männlich - '.$dcup['u']).'</li>'.
  '<li>'.FSS::dis2img('hl').' '.Link::dcup_single($year, 'hlu', 'Hakenleitersteigen - '.$dcup['u']).'</li>'.
  '<li>'.FSS::dis2img('zk').' '.Link::dcup_single($year, 'zku', 'Zweikampf - '.$dcup['u']).'</li>'.
  '</ul>';
}

echo Bootstrap::row()
->col(
  '<p>Diese Seite zeigt die Gesamtwertung der D-Cup-Ergebnisse. Dabei handelt es sich um selbst berechnete Daten, welche <strong>nicht offiziell</strong> sind.</p>'.
  '<p>Die Einzelergebnisse stehen auch zur Verfügung. Die Ergebnisse der Jugend kommen bei Bedarf auch noch mit rein. Die Zweikampfwertung wurde erst 2013 offiziell eingeführt.</p>', 6)
->col('<ul>'.
  '<li>'.FSS::dis2img('hb').' '.Link::dcup_single($year, 'hbf', 'Hindernisbahn weiblich').'</li>'.
  '<li>'.FSS::dis2img('hb').' '.Link::dcup_single($year, 'hbm', 'Hindernisbahn männlich').'</li>'.
  '<li>'.FSS::dis2img('hl').' '.Link::dcup_single($year, 'hl', 'Hakenleitersteigen').'</li>'.
  '<li>'.FSS::dis2img('zk').' '.Link::dcup_single($year, 'zk', 'Zweikampf').'</li>'.
  '</ul>'.$under, 6);
echo Bootstrap::row()->col('<p>Zu jedem Wettkampf stehen die einzelnen Punkte für die Disziplin und darunter die addierten Werte. Ganz rechts ist dann die Gesamtanzahl der Punkte zu finden. Die Mannschaften sind absteigend nach Punkte geordnet. Nach der <a href="http://www.feuerwehrsport-teammv.de/wp-content/uploads/2013/08/Ausschreibung-Deutschland-Cup-2013-DFV.pdf">Ausschreibung</a> gilt bei Punktgleichheit die bessere Löschangriff-Zeit.', 12);

$navTab = Bootstrap::navTab();

foreach (array('female', 'male') as $sex) {
  list($teams, $competitions) = DcupCalculation::getTeamScores($sex, $id);

  $output = '<table class="d-cup-wertung">';
  $output .= '<tr><th class="right-line">Team</th>';
  foreach ($competitions as $competition) {
    $output .= '<th colspan="3" class="right-line competition">'.Link::competition($competition['competition_id'], $competition['place']).'</th>';
  }
  $output .= '<th class="result sum">Summe</th></tr>';

  foreach ($teams as $team) {
    $max = $team->getMaxLines();
    for ($line = 0; $line < $max; $line++) {
      $output .= '<tr';
      if ($line == 0) {
        $output .= ' class="top2-line"><th class="right-line team" rowspan="'.($max+ 1).'">'.Link::team($team->id, $team->name).'</th>';
      } else {
        $output .= '>';
      }

      foreach ($competitions as $competition) {
        $score = $team->getScore($competition['competition_id'], $line);
        if ($score) {
          $output .= '<td>'.$score['discipline'].'</td><td>'.
          $score['points'].'</td><td class="right-line">'.FSS::time($score['time']).'</td>';
        } else {
          $output .= '<td colspan="3" class="right-line"></td>';
        }
      }
      $output .= '<td></td></tr>';
    }
    $output .= '<tr>';
    foreach ($competitions as $competition) {
      $sum = $team->getSum($competition['competition_id']);
      $class = ' top-line ';
      if ($sum == 0) {
        $sum = '';
        $class = '';
      }
      $output .= '<td colspan="2" class="'.$class.'"></td><th class="right-line result'.$class.'">'.$sum.'</th>';
    }
    $output .= '<th class="top-line result sum">'.$team->getSum().'</th></tr>';
  }
  $output .= '</table>';

  $navTab->tab('Gesamtwertung '.$year.' - '.FSS::sex($sex), $output);
}

echo $navTab;
