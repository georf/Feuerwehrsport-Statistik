<?php

if (!Check::get('id') || !preg_match('|^[1,2][0-9]{3}$|', $_GET['id'])) throw new PageNotFound();
$_year = $_GET['id'];

echo Title::set('Überblick für Jahr '.$_year);

echo '<div class="six columns">';
echo '<ul class="disc">';
echo '<li>'.Link::bestScoresOfYear($_year, 'Bestzeiten des Jahres').'</li>';
echo '<li>'.Link::bestPerformanceOfYear($_year, 'Bestleistungen des Jahres').'</li>';
echo '</ul></div>';
echo '<div class="seven columns">';
echo '<p>Diese Seite zeigt Statistiken über das Jahr '.$_year.'. Für Einzeldisziplinen sind die Unterseiten '.Link::bestScoresOfYear($_year, 'Bestzeiten des Jahres').' und '.Link::bestPerformanceOfYear($_year, 'Bestleistungen des Jahres').' interessant.</p>';
echo '</div>';

TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hl');
TempDB::generate('x_full_competitions');

$individual = 0;
$total_hbf = 0;
$total_hbm = 0;
$total_hl = 0;
$total_laf = 0;
$total_lam = 0;
$total_fsf = 0;
$total_fsm = 0;
$total_gs = 0;

$competitions = $db->getRows("
  SELECT *
  FROM `x_full_competitions`
  WHERE YEAR(`date`) = '".$_year."'
  ORDER BY `date` DESC;
");

foreach ($competitions as $competition) {
  $hbm = $db->getFirstRow("
    SELECT COUNT(`id`) AS `count`
    FROM `x_scores_hbm`
    WHERE `competition_id` = '".$competition['id']."'
  ", 'count');
  $total_hbm += $hbm;

  $hbf = $db->getFirstRow("
    SELECT COUNT(`id`) AS `count`
    FROM `x_scores_hbf`
    WHERE `competition_id` = '".$competition['id']."'
  ", 'count');
  $total_hbf += $hbf;

  $gs = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_gs`
    WHERE `competition_id` = '".$competition['id']."'
  ", 'count');
  $total_gs += $gs;

  $laf = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_la`
    WHERE `competition_id` = '".$competition['id']."'
    AND `sex` = 'female'
  ", 'count');
  $total_laf += $laf;

  $lam = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_la`
    WHERE `competition_id` = '".$competition['id']."'
    AND `sex` = 'male'
  ", 'count');
  $total_lam += $lam;

  $fsf = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_fs`
    WHERE `competition_id` = '".$competition['id']."'
    AND `sex` = 'female'
  ", 'count');
  $total_fsf += $fsf;

  $fsm = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_fs`
    WHERE `competition_id` = '".$competition['id']."'
    AND `sex` = 'male'
  ", 'count');
  $total_fsm += $fsm;

  $hl = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `x_scores_hl`
    WHERE `competition_id` = '".$competition['id']."'
  ", 'count');
  $total_hl += $hl;

  $individual += $hl + $hbm + $hbf;
}

echo '
<h2>Auswertung</h2>
<div class="row">
    <div class="five columns">
        <h4>Verteilung der Wettkämpfe über das Jahr</h4>'.Chart::img('overview_month', array($_year, 'year')).'
    </div>
    <div class="five columns">
        <h4>Verteilung der Wettkämpfe über die Woche</h4>'.Chart::img('overview_week', array($_year, 'year')).'
    </div>
    <div class="five columns">
        <h4>Angebotene Disziplinen pro Wettkampf</h4>'.Chart::img('competitions_score_types', array($_year, 'year')).'
    </div>
</div>
<div class="row">
    <div class="five columns">
        <h4>Anzahl der Mannschaften pro Wettkampf</h4>'.Chart::img('competitions_team_counts', array($_year, 'year')).'
    </div>';

if ($individual > 0) {
    echo '
    <div class="five columns">
        <h4>Mannschaftswertungen der Einzeldisziplinen</h4>'.Chart::img('competitions_team_scores', array($_year, 'year')).'
    </div>
    <div class="five columns">
        <h4>Anzahl der Einzelstarter pro Wettkampf</h4>'.Chart::img('competitions_person_counts', array($_year, 'year')).'
    </div>';
}
echo '
</div>

<h2>Disziplinen</h2>';

$disciplines = array(
  'hbf' => $total_hbf,
  'hbm' => $total_hbm,
  'hl' => $total_hl,
);

foreach ($disciplines as $d => $total) {
  switch ($d) {
    case 'hbf':
      $sex = 'female';
      $dis = 'hb';
      break;
    case 'hbm':
      $sex = 'male';
      $dis = 'hb';
      break;
    default:
    case 'hl':
      $sex = 'male';
      $dis = 'hl';
      break;
  }

  if ($total > 0) {

    $avg = $db->getFirstRow("
      SELECT AVG(`s`.`time`) AS `avg`
      FROM `persons` `p`
      INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE `s`.`discipline` = '".$db->escape($dis)."'
      AND `p`.`sex` = '".$sex."'
      AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
      AND `time` IS NOT NULL
    ", 'avg');

    $best = $db->getFirstRow("
      SELECT `s`.`time`, `p`.`id`, `s`.`competition_id`
      FROM `persons` `p`
      INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      WHERE `s`.`discipline` = '".$db->escape($dis)."'
      AND `p`.`sex` = '".$sex."'
      AND `s`.`time` IS NOT NULL
      AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
      ORDER BY `s`.`time`
    ");
    $c = FSS::competition($best['competition_id']);

    echo '
<div class="row hideshow" style="border:1px solid #BAE0F1">
  <div class="eleven columns headline">
    <h3>'.FSS::dis2img($dis).' '.FSS::dis2name($dis).' - '.FSS::sex($sex).'</h3>
  </div>
  <div class="five columns">
    <table>
      <tr><th>Durchschnitt</th><td>',FSS::time($avg),' s</td></tr>
      <tr><th>Anzahl</th><td>',$total,'</td></tr>
      <tr><th>Bestzeit</th><td>',FSS::time($best['time']),' s<br/>',Link::person($best['id'], 'full'),'<br/>',Link::competition($c['id'], gDate($c['date'])).' '.Link::event($c['event_id'], $c['event']).'</td></tr>
    </table>
  </div>
</div>';
    }
}

$disciplines = array(
  'female' => $total_fsf,
  'male' => $total_fsm,
);

foreach ($disciplines as $sex => $total) {
  if ($total > 0) {
    echo '
      <div class="row hideshow" style="border:1px solid #BAE0F1">
        <div class="eleven columns headline">
          <h3>'.FSS::dis2img('fs').' Feuerwehrstafette - '.FSS::sex($sex).'</h3>
        </div>';

    $types = array(
      'Feuer' => ' = \'feuer\'',
      'Abstellen' => ' = \'abstellen\'',
      'unbekannt' => ' IS NULL',
    );

    foreach ($types as $t_name => $t_type) {
      $count = $db->getFirstRow("
        SELECT COUNT(`s`.`id`) AS `count`
        FROM `scores_fs` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        WHERE `s`.`sex` = '".$sex."'
        AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
        AND `c`.`fs` ".$t_type."
      ", 'count');

      if ($count <= 0) continue;

      $avg = $db->getFirstRow("
        SELECT AVG(`s`.`time`) AS `avg`
        FROM `scores_fs` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        WHERE `s`.`sex` = '".$sex."'
        AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
        AND `c`.`fs` ".$t_type."
        AND `time` IS NOT NULL
      ", 'avg');

      $best = $db->getFirstRow("
        SELECT `s`.*
        FROM `scores_fs` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        WHERE `s`.`sex` = '".$sex."'
        AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
        AND `c`.`fs` ".$t_type."
        AND `time` IS NOT NULL
        ORDER BY `s`.`time`
      ");
      $c = FSS::competition($best['competition_id']);

      echo '
      <div class="six columns">
        <h4>Typ: ',$t_name,'</h4>
        <img src="/styling/images/fs-'.strtolower($t_name).'.png" alt=""/>
      </div>
      <div class="six columns">
        <table>
          <tr><th colspan="2">Durchschnitt</th><td>',FSS::time($avg),' s</td></tr>
          <tr><th colspan="2">Anzahl</th><td>',$count,'</td></tr>
          <tr><th colspan="2">Bestzeit</th><td>',FSS::time($best['time']),' s<br/>',Link::team($best['team_id']),'<br/>',Link::competition($c['id'], gDate($c['date'])).' '.Link::event($c['event_id'], $c['event']).'</td></tr>
        </table>
      </div>';
    }
    echo '</div>';
  }
}

if ($total_gs > 0) {
  echo '
    <div class="row hideshow" style="border:1px solid #BAE0F1">
      <div class="eleven columns headline">
        <h3>'.FSS::dis2img('gs').' Gruppenstafette</h3>
      </div>';

  $avg = $db->getFirstRow("
    SELECT AVG(`s`.`time`) AS `avg`
    FROM `scores_gs` `s`
    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
    AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
    AND `time` IS NOT NULL
  ", 'avg');

  $best = $db->getFirstRow("
    SELECT `s`.*
    FROM `scores_gs` `s`
    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
    AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
    AND `time` IS NOT NULL
    ORDER BY `s`.`time`
  ");
  $c = FSS::competition($best['competition_id']);

  echo '
    <div class="six columns">
      <table>
        <tr><th colspan="2">Durchschnitt</th><td>',FSS::time($avg),' s</td></tr>
        <tr><th colspan="2">Anzahl</th><td>',$total_gs,'</td></tr>
        <tr><th colspan="2">Bestzeit</th><td>',FSS::time($best['time']),' s<br/>',Link::team($best['team_id']),'<br/>',Link::competition($c['id'], gDate($c['date'])).' '.Link::event($c['event_id'], $c['event']).'</td></tr>
      </table>
    </div>
  </div>';
}


$disciplines = array(
  'female' => $total_laf,
  'male' => $total_lam,
);

foreach ($disciplines as $sex => $total) {
  if ($total > 0) {
    echo '
      <div class="row hideshow" style="border:1px solid #BAE0F1">
        <div class="eleven columns headline">
          <h3>'.FSS::dis2img('la').' Löschangriff - '.FSS::sex($sex).'</h3>
        </div>';

    $avg = $db->getFirstRow("
      SELECT AVG(`s`.`time`) AS `avg`
      FROM `scores_la` `s`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
      AND `time` IS NOT NULL
      AND `sex` = '".$sex."'
    ", 'avg');

    $best = $db->getFirstRow("
      SELECT `s`.*
      FROM `scores_la` `s`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      AND YEAR(`c`.`date`) = '".$db->escape($_year)."'
      AND `time` IS NOT NULL
      AND `sex` = '".$sex."'
      ORDER BY `s`.`time`
    ");
    $c = FSS::competition($best['competition_id']);


    echo '
      <div class="six columns">
        <table>
          <tr><th colspan="2">Durchschnitt</th><td>',FSS::time($avg),' s</td></tr>
          <tr><th colspan="2">Anzahl</th><td>',$total_gs,'</td></tr>
          <tr><th colspan="2">Bestzeit</th><td>',FSS::time($best['time']),' s<br/>',Link::team($best['team_id']),'<br/>',Link::competition($c['id'], gDate($c['date'])).' '.Link::event($c['event_id'], $c['event']).'</td></tr>
        </table>
      </div>
    </div>';
  }
}
