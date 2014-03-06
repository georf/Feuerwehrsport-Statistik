<?php

echo Title::set('D-Cup Gesamtwertung');

echo Bootstrap::row()
->col(
  '<p>Diese Seite zeigt die Gesamtwertung der D-Cup-Ergebnisse. Dabei handelt es sich um selbst berechnete Daten, welche <strong>nicht offiziell</strong> sind.</p>'.
  '<p>Die Einzelergebnisse stehen jetzt auch zur Verfügung. Die Ergebnisse der Jugend kommen bei Bedarf auch noch mit rein.</p>', 6)
->col('<ul>'.
  '<li>'.FSS::dis2img('hb').' '.Link::dcup_single('2013', 'hbf', 'Hindernisbahn weiblich').'</li>'.
  '<li>'.FSS::dis2img('hb').' '.Link::dcup_single('2013', 'hbm', 'Hindernisbahn männlich').'</li>'.
  '<li>'.FSS::dis2img('hl').' '.Link::dcup_single('2013', 'hl', 'Hakenleitersteigen').'</li>'.
  '<li>'.FSS::dis2img('zk').' '.Link::dcup_single('2013', 'zk', 'Zweikampf').'</li>'.
  '</ul>', 6);
echo Bootstrap::row()->col('<p>Zu jedem Wettkampf stehen die einzelnen Punkte für die Disziplin und darunter die addierten Werte. Ganz rechts ist dann die Gesamtanzahl der Punkte zu finden. Die Mannschaften sind absteigend nach Punkte geordnet. Nach der <a href="http://www.feuerwehrsport-teammv.de/wp-content/uploads/2013/08/Ausschreibung-Deutschland-Cup-2013-DFV.pdf">Ausschreibung</a> gilt bei Punktgleichheit die bessere Löschangriff-Zeit. Dies wird hier <strong>nicht</strong> beachtet.</p>', 12);

$years = $db->getRows("
  SELECT YEAR(`c`.`date`) AS `year`
  FROM `scores_team_dcup` `s`
  INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
  GROUP BY `year`
  ORDER BY `year` DESC
", 'year');

foreach ($years as $year) {
  foreach (array('female', 'male') as $sex) {
    echo Title::h2('Gesamtwertung '.$year.' - '.FSS::sex($sex));
    $rows = $db->getRows("
      SELECT `s`.`points`,
        `c`.`date`, `s`.`competition_id`,
        `c`.`event_id`, `e`.`name` AS `event`,
        `c`.`place_id`, `p`.`name` AS `place`,
        `s`.`team_id`, `s`.`team_number`, `t`.`short` AS `team`,
        `s`.`discipline`
      FROM `scores_team_dcup` `s`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
      INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
      INNER JOIN `teams` `t` ON `s`.`team_id` = `t`.`id`
      WHERE `s`.`sex` = '".$sex."'
      AND YEAR(`c`.`date`) = '".$year."'
      ORDER BY `team_id`,`competition_id`,`discipline`
    ");

    $competitions = array();
    $teams = array();

    foreach ($rows as $row) {
      if (!isset($competitions[$row['competition_id']])) {
        $competitions[$row['competition_id']] = $row;
      }

      if (!isset($teams[$row['team_id'].'-'.$row['team_number']])) {
        $teams[$row['team_id'].'-'.$row['team_number']] = new DCupTeam($row);
      }

      $teams[$row['team_id'].'-'.$row['team_number']]->addScore($row);
    }

    uasort($teams, function($a, $b) {
        return $a->getSum() < $b->getSum();
    });

    echo '<table class="d-cup-wertung">';
    echo '<tr><th class="right-line">Team</th>';
    foreach ($competitions as $competition) {
        echo '<th colspan="2" class="right-line">'.Link::competition($competition['competition_id'], $competition['place']).'</th>';
    }
    echo '<th>Summe</th></tr>';

    foreach ($teams as $team) {
      $max = $team->getMaxLines();
      for ($line = 0; $line < $max; $line++) {
        echo '<tr';
        if ($line == 0) {
          echo ' class="top2-line"><th class="right-line" rowspan="'.($max+ 1).'">'.Link::team($team->id, $team->name).'</th>';
        } else {
          echo '>';
        }

        foreach ($competitions as $competition) {
          $score = $team->getScore($competition['competition_id'], $line);
          echo '<td>'.$score['discipline'].'</td><td class="right-line">'.
          $score['points'].'</td>';
        }
        echo '</tr>';
      }
      echo '<tr>';
      foreach ($competitions as $competition) {
        echo '<td class="top-line"></td><th class="right-line top-line">'.$team->getSum($competition['competition_id']).'</th>';
      }
      echo '<th class="top-line">'.$team->getSum().'</th></tr>';
    }
    echo '</table>';
  }
}
