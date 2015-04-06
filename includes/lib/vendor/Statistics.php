<?php

class Statistics {
  public static function calculatePersons($year, $discipline, $sex) {
    global $db;
    $persons = array();

    $scores = $db->getRows("
      SELECT `s`.*, `e`.`name` AS `event`
      FROM `scores` `s`
      INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
      INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
      INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
      WHERE YEAR(`c`.`date`) = '".$db->escape($year)."'
      AND `discipline` = '".$discipline."'
      AND `p`.`sex` = '".$sex."'
      AND `p`.`nation_id` = 1
    ");

    foreach ($scores as $score) {
      if (!isset($persons[$score['person_id']])) {
        $persons[$score['person_id']] = array(
          'scores'   => array(),
          'avg'      => FSS::INVALID,
          'calc'     => FSS::INVALID,
          'count'    => 0,
          'invalids' => 0,
          'id'       => $score['person_id']
        );
      }
      $persons[$score['person_id']]['scores'][] = $score;
    }

    foreach ($persons as $personId => $person) {
      $sum = 0;
      $Ds = 0;
      $count = 0;

      foreach ($person['scores'] as $s) {
        if (FSS::isInvalid($s['time'])) {
          $Ds++;
        } else {
          $sum += intval($s['time']);
          $count++;
        }
      }
      if ($count != 0) {
        $persons[$personId]['avg'] = $sum/$count;
      }

      //- 1/23 *x^2+ 10
      $sum = 0;
      for ($z = 0; $z < $count; $z++) {
        $s = -1/23 * pow($z, 2) + 10;
        if ($s < 0) break;
        $sum += $s;
      }
      $persons[$personId]['calc']    = $persons[$personId]['avg'] + $Ds*15 - $sum;
      $persons[$personId]['count']   = $count;
      $persons[$personId]['invalid'] = $Ds;
    }

    uasort($persons, function($a, $b) {
        return ($a['calc'] > $b['calc']);
    });
    return $persons;
  }

  public static function calculateTeams($year, $discipline, $sex) {
    global $db;
    $teams = array();
    $where = ($sex)? "AND `sex` = '".$sex."'" : "";

    $scores = $db->getRows("
      SELECT `gs`.*, `e`.`name` AS `event`, `gsc`.`competition_id`
      FROM `group_scores` `gs`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      INNER JOIN `competitions` `c` ON `c`.`id` = `gsc`.`competition_id`
      INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
      WHERE YEAR(`c`.`date`) = '".$db->escape($year)."'
      AND `gst`.`discipline` = '".$discipline."'
      ".$where."
    ");

    foreach ($scores as $score) {
      if (!isset($teams[$score['team_id'].'-'.$score['team_number']])) {
        $teams[$score['team_id'].'-'.$score['team_number']] = array(
          'scores'   => array(),
          'avg'      => FSS::INVALID,
          'calc'     => FSS::INVALID,
          'count'    => 0,
          'invalids' => 0,
          'id'       => $score['team_id']
        );
      }
      $teams[$score['team_id'].'-'.$score['team_number']]['scores'][] = $score;
    }

    foreach ($teams as $teamId => $team) {
      $sum = 0;
      $Ds = 0;
      $count = 0;

      foreach ($team['scores'] as $s) {
        if (FSS::isInvalid($s['time'])) {
          $Ds++;
        } else {
          $sum += intval($s['time']);
          $count++;
        }
      }
      if ($count != 0) {
        $teams[$teamId]['avg'] = $sum/$count;
      }

      //- 1/23 *x^2+ 10
      $sum = 0;
      for ($z = 0; $z < $count; $z++) {
        $s = -1/23 * pow($z, 2) + 10;
        if ($s < 0) break;
        $sum += $s;
      }
      $teams[$teamId]['calc']    = $teams[$teamId]['avg'] + $Ds*15 - $sum;
      $teams[$teamId]['count']   = $count;
      $teams[$teamId]['invalid'] = $Ds;
    }
    uasort($teams, function($a, $b) {
      return ($a['calc'] > $b['calc']);
    });
    return $teams;
  }
}
