<?php
class Analysis {
  public static function generalCharts($type = false, $id = false) {
    global $db;

    TempDB::generate('x_full_competitions');

    $options = array();
    if ($id) $options[] = $id;
    if ($type) $options[] = $type;
    $output = Bootstrap::row()
      ->col('<h4>Verteilung der Wettkämpfe über das Jahr</h4>'.Chart::img('overview_month', $options), 4)
      ->col('<h4>Verteilung der Wettkämpfe über die Woche</h4>'.Chart::img('overview_week', $options), 4)
      ->col('<h4>Angebotene Disziplinen pro Wettkampf</h4>'.Chart::img('competitions_score_types', $options), 4);
    
    if ($type && $type != 'year') {
      $individual = $db->getFirstRow("
        SELECT 1
        FROM `scores` `s`
        INNER JOIN `x_full_competitions` `c` ON `s`.`competition_id` = `c`.`id`
        WHERE `".$type."_id` = '".$id."'
        LIMIT 1
      ", 'exists');
    }
    if (!$type || $type == 'year' || $individual) {
      $output .= Bootstrap::row()
        ->col('<h4>Mannschaftswertungen der Einzeldisziplinen</h4>'.Chart::img('competitions_team_scores', $options), 4)
        ->col('<h4>Anzahl der Mannschaften pro Wettkampf</h4>'.Chart::img('competitions_team_counts', $options), 4)
        ->col('<h4>Anzahl der Einzelstarter pro Wettkampf</h4>'.Chart::img('competitions_person_counts', $options), 4);
    }
    return $output;
  }

  public static function bestOfYears($type = false, $id = false) {
    global $db;

    if ($type != 'year') {
      $years = $db->getRows("
        SELECT YEAR(`date`) AS `year`
        FROM `competitions` 
        ".(($type)? "WHERE `".$type."_id` = '".$id."'":"")."
        GROUP BY `year`
        ORDER BY `year` DESC
      ", 'year');
    } else {
      $years = array();
    }


    TempDB::generate('x_scores_hbf');
    TempDB::generate('x_scores_hbm');
    TempDB::generate('x_scores_hlf');

    $disciplines = array(
      array('hb', false, 'female', 'x_scores_hbf'),
      array('hb', false, 'male',   'x_scores_hbm'),
      array('hl', false, 'female', 'x_scores_hlf'),
      array('hl', false, 'male',   'x_scores_hlm'),
      array('gs', true,  false,    false),
      array('la', true,  'female', false),
      array('la', true,  'male',   false),
      array('fs', true,  'female', false),
      array('fs', true,  'male',   false),
    );
    $navTab = Bootstrap::navTab();

    foreach (FSS::$disciplines as $discipline) {
      $group      = FSS::isGroupDiscipline($discipline);
      foreach (FSS::$sexes as $sex) {
        $wheres = array("`time` IS NOT NULL");
        if ($type && $type != 'year') $wheres[] = "`c`.`".$type."_id` = '".$db->escape($id)."'";
        if ($type == 'year')          $wheres[] = "YEAR(`date`) = '".$id."'";


        if (!$group) {
          $table = 'x_scores_'.$discipline.substr($sex, 0, 1);
          TempDB::generate($table); 
          $output = self::bestOfYearTab($type, $id, $group, $table, $wheres, $years);
        } else {
          $table = "(
            SELECT `gs`.*, `competition_id`, `gst`.`id` AS `gst_id`
            FROM `group_scores` `gs` 
            INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
            INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
            WHERE `gst`.`discipline` = '".$discipline."'
            AND `gs`.`sex` = '".$sex."'
          )";
          $types = $db->getRows("
            SELECT `gst`.*
            FROM `group_scores` `gs` 
            INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
            INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
            INNER JOIN `competitions` `c` ON `gsc`.`competition_id` = `c`.`id`
            WHERE `gst`.`discipline` = '".$discipline."'
            AND `gs`.`sex` = '".$sex."'
            AND ".implode(" AND ", $wheres)."
            GROUP BY `gst`.`id`
            ORDER BY `gst`.`regular` DESC
          ");
          $outputs = array();
          foreach ($types as $groupType) {
            $wheres[] = "`gst_id` = ".$groupType['id'];
            $outputs[] = self::bestOfYearTab(
              $type, 
              $id, 
              $group, 
              $table, 
              $wheres, 
              $years,
              '<h4>Typ: '.$groupType['name'].'</h4>'
            );
            array_pop($wheres);
          }
          $output = implode("<hr/>", $outputs);
        }
        if (empty($output)) continue;
        $headline = FSS::dis2img($discipline).' '.strtoupper($discipline).' '.FSS::sexSymbol($sex);
        $navTab->tab($headline, $output, FSS::dis2name($discipline).' '.FSS::sex($sex));
      }
    }
    return $navTab;
  }

  private static function bestOfYearTab($type, $id, $group, $table, $wheres, $years, $prepend = "") {
    global $db;
    
    TempDB::generate('x_full_competitions');

    $joins = array();
    if (!$group) $joins[] = "INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id` AND `p`.`nation_id` = 1";

    $result = $db->getFirstRow("
      SELECT AVG(`s`.`time`) AS `avg`, COUNT(`s`.`time`) AS `total`
      FROM ".$table." `s` 
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      ".implode(" ", $joins)."
      WHERE ".implode(" AND ", $wheres)."
      ORDER BY `time`
    ");
    if ($result['total'] == 0) return '';

    $best = $db->getFirstRow("
      SELECT `competition_id`, `date`, `event_id`, `event`, `time`,
      `".(($group)?'team_id':'person_id')."`
      FROM ".$table." `s` 
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
      ".implode(" ", $joins)."
      WHERE ".implode(" AND ", $wheres)."
      ORDER BY `time`
    ");

    $c = FSS::competition($best['competition_id']);
    $output = $prepend.
      '<table class="table">'.
        '<tr><th>Durchschnitt</th><td>'.FSS::time($result['avg']).' s</td></tr>'.
        '<tr><th>Anzahl</th><td>'.$result['total'].'</td></tr>'.
        '<tr><th>Bestzeit</th><td>'.FSS::time($best['time']).' s<br/>'.
          (($group)?Link::team($best['team_id']):Link::person($best['person_id'], 'full')).
          '<br/>'.
          Link::competition($c['id'], gDate($c['date'])).' '.
          Link::event($c['event_id'], $c['event']).
          '</td></tr>'.
      '</table>';

    $output2 = '<table class="table">';
    foreach ($years as $year) {
      $best = $db->getFirstRow("
        SELECT `competition_id`, `date`, `event_id`, `event`, `time`,
        `".(($group)?'team_id':'person_id')."`
        FROM ".$table." `s` 
        INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
        ".implode(" ", $joins)."
        WHERE ".implode(" AND ", $wheres)."
        AND YEAR(`c`.`date`) = ".$year."
        ORDER BY `time`
      ");
      if ($best) {
        $output2 .= 
          '<tr><th>'.
            $year.
            '</th><td>'.FSS::time($best['time']).' s</td><td>'.
            (($group)?Link::team($best['team_id']):Link::person($best['person_id'], 'full')).
            '<br/>'.
            Link::competition($best['competition_id'], gDate($best['date'])).
          '</td></tr>';
      }
    }
    $output2 .= '</table>';

    return Bootstrap::row()->col($output,6)->col($output2,6);
  }
}