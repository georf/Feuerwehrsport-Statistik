<?php
class DcupCalculation {

  public static function getSingleScores($personId, $dcupId, $discipline, $sort = 'time', $under = false) {
    global $db;
    TempDB::generate('x_full_competitions');
    
    $u = ($under) ? '_u' : '';

    if ($discipline == 'zk') {
      return $db->getRows("
        SELECT `competition_id`,`time`,`hl`,`hb`,`points`,`place`,`date`
        FROM `scores_dcup_zk".$u."` `s`
        INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
        WHERE `dcup_id` = '".$dcupId."'
          AND `person_id` = '".$personId."'
        ORDER BY `".$sort."`
      ");
    } else {
      return $db->getRows("
        SELECT `s`.`competition_id`,`s`.`time`,`d`.`points`,`place`,`date`
        FROM `scores_dcup_single".$u."` `d`
        INNER JOIN (    
          SELECT COALESCE(`time`, ".FSS::INVALID.") AS `time`,`competition_id`,`id`
          FROM `scores`
          WHERE `discipline` = '".$discipline."'
          AND `person_id` = '".$personId."'
          AND `team_number` > -2
        ) `s` ON `d`.`score_id` = `s`.`id`
        INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
        WHERE `d`.`dcup_id` = '".$dcupId."'
        ORDER BY `".$sort."`
      ");
    }
  }

  public static function notReadyBox($dcup) {
    if (!$dcup['ready']) {
      return Bootstrap::row("jumbotron")
      ->col('<img style="" src="/styling/images/warning.png"/>', 2)
      ->col('<p>Die D-Cup-Wertung von dem Jahr '.$dcup['year'].' wurde noch nicht abgeschlossen. Die hier gezeigten Ergebnisse sind nicht endgültig.</p>', 10);
    }
    return '';
  }

  public static function zkYouth($competitionId, $dcupId, $personIds) {
    global $db;

    foreach (FSS::$sexes as $sex) {
      $scores = $db->getRows("
        SELECT zk.*
        FROM scores_dcup_zk zk
        INNER JOIN `persons` `p` ON `zk`.`person_id` = `p`.`id`
        WHERE zk.person_id IN ('".implode("','", $personIds)."')
        AND `sex` = '".$sex."'
        AND zk.competition_id = '".$competitionId."'
        GROUP BY zk.id
      ");
      self::zkSortAndPoint($competitionId, $dcupId, $scores, true);
    }
  }
      

  public static function zk($competitionId, $dcupId) {
    global $db;

    foreach (FSS::$sexes as $sex) {

      $scores = $db->getRows("
        SELECT `hb`.`person_id`, `hb`.`time` AS `hb`, `hl`.`time` AS `hl`, 
          `hl`.`time` + `hb`.`time` AS `time`
        FROM (
          SELECT `person_id`,
            MIN(COALESCE(`time`, ".FSS::INVALID.")) AS `time`
          FROM `scores`
          WHERE `competition_id` = '".$competitionId."'
            AND `discipline` = 'HL'
            AND `team_number` > -2
          GROUP BY `person_id`
        ) `hl`
        INNER JOIN (
          SELECT `person_id`,
            MIN(COALESCE(`time`, ".FSS::INVALID.")) AS `time`
          FROM `scores`
          WHERE `competition_id` = '".$competitionId."'
            AND `discipline` = 'HB'
            AND `team_number` > -2
          GROUP BY `person_id`
        ) `hb` ON `hl`.`person_id` = `hb`.`person_id`
        INNER JOIN `persons` `p` ON `p`.`id` = `hb`.`person_id` AND `p`.`sex` = '".$sex."'
        GROUP BY `person_id`
        ORDER BY `time`
      ");
      self::zkSortAndPoint($competitionId, $dcupId, $scores);
    }
  }

  private static function zkSortAndPoint($competitionId, $dcupId, $scores, $youth = false) {
    global $db;

    foreach ($scores as $key => $score) {
      $scores[$key]['time'] = min($score['time'], FSS::INVALID);
      $scores[$key]['other'] = array($scores[$key]['time']);
    }

    $scores = self::sortSingle($scores);
    $scores = self::givePoints($scores);
    $table = "scores_dcup_zk";
    if ($youth) {
      $table .= '_u';
    }

    foreach ($scores as $score) {
      $db->insertRow($table, array(
        'dcup_id' => $dcupId,
        'person_id' => $score['person_id'],
        'competition_id' => $competitionId,
        'points' => $score['points'],
        'time' => $score['time'],
        'hl' => $score['hl'],
        'hb' => $score['hb'],
      ), false);
    }
  }

  public static function singleYouth($competitionId, $personIds, $discipline, $sex) {
    global $db;

    $scores = $db->getRows("
      SELECT s.*
      FROM scores_dcup_single ds
      INNER JOIN scores s ON s.id = ds.score_id
      INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
      WHERE s.person_id IN ('".implode("','", $personIds)."')
      AND `discipline` = '".$discipline."'
      AND `sex` = '".$sex."'
      AND s.competition_id = '".$competitionId."'
      GROUP BY s.id
    ");
    return self::sortAndPoint($scores, $competitionId, $discipline, $sex);
  }
    
  public static function single($competitionId, $discipline, $sex) {
    global $db;
    
    $scores = $db->getRows("
      SELECT `best`.*
      FROM (
        SELECT *
        FROM (
          SELECT `id`,
            `person_id`,
            COALESCE(`time`, ".FSS::INVALID.") AS `time`
          FROM `scores`
          WHERE `competition_id` = '".$competitionId."'
            AND `discipline` = '".$discipline."'
            AND `team_number` > -2
          ORDER BY `time`
        ) `all`
        GROUP BY `person_id`
      ) `best`
      INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
      WHERE `sex` = '".$sex."'
      ORDER BY `time`
    ");
    return self::sortAndPoint($scores, $competitionId, $discipline, $sex);
  }

  private static function sortAndPoint($scores, $competitionId, $discipline, $sex) {
    global $db;

    foreach ($scores as $key => $score) {
      $scores[$key]['other'] = $db->getRows("
        SELECT COALESCE(`time`, ".FSS::INVALID.") AS `time`
        FROM `scores`
        WHERE `competition_id` = '".$competitionId."'
          AND `person_id` = '".$score['person_id']."'
          AND `discipline` = '".$discipline."'
          AND `team_number` > -2
        ORDER BY `time`
      ", 'time');
    }

    $scores = self::sortSingle($scores);
    return self::givePoints($scores);
  }

  public static function givePoints($scores) {
    $points = 30;
    for ($i=0; $i < count($scores); $i++) {
      $scores[$i]['points'] = $points;
      if ($i > 0 && self::equal($scores[$i], $scores[$i-1])) {
        $scores[$i]['points'] = $scores[$i-1]['points'];
      }
      if ($points > 0) $points--;
    }
    return $scores;
  }

  public static function insertSingle($scores, $dcupId, $youth = false) {
    global $db;
    $table = "scores_dcup_single";
    if ($youth) {
      $table .= "_u";
    }
    foreach ($scores as $score) {
      $db->insertRow($table, array(
        'dcup_id' => $dcupId,
        'score_id' => $score['id'],
        'points' => $score['points'],
      ), false);
    }
  }

  public static function calculate($under = false) {
    global $db;

    $u = ($under) ? '_u' : '';
    $where = ($under) ? " WHERE `u` IS NOT NULL" : '';

    $db->query("TRUNCATE TABLE `dcup_points".$u."`");

    $disciplines = array(
      array('HL', 'male'),
      array('HL', 'female'),
      array('HB', 'male'),
      array('HB', 'female'),
      array('ZK', 'male'),
      array('ZK', 'female'),
    );

    $compare = function($a , $b) {
      if ($a['points'] < $b['points']) return 1;
      if ($a['points'] > $b['points']) return -1;

      if ($a['participations'] < $b['participations']) return 1;
      if ($a['participations'] > $b['participations']) return -1;

      if ($a['time'] > $b['time']) return 1;
      if ($a['time'] < $b['time']) return -1;

      return 0;
    };

    foreach ($disciplines as $discipline) {
      foreach ($db->getRows("SELECT `id` FROM `dcups`".$where, 'id') as $dcupId) {
        if ($discipline[0] == 'ZK') {
          $persons = $db->getRows("
            SELECT 
              SUM(`points`) AS `points`, 
              `person_id`, 
              COUNT(`d`.`id`) AS `participations`,
              SUM(`time`) AS `time`
            FROM `scores_dcup_zk".$u."` `d`
            INNER JOIN `persons` `p` ON `p`.`id` = `d`.`person_id`
            WHERE `dcup_id` = '".$dcupId."'
              AND `p`.`sex` = '".$discipline[1]."'
            GROUP BY `person_id`
          ");
        } else {
          $persons = $db->getRows("
            SELECT 
              SUM(`points`) AS `points`, 
              `person_id`, 
              COUNT(`s`.`id`) AS `participations`,
              SUM(`time`) AS `time`
            FROM `scores_dcup_single".$u."` `d`
            INNER JOIN (
              SELECT COALESCE(`time`, ".FSS::INVALID.") AS `time`,
                `id`,
                `person_id`
              FROM `scores`
              WHERE `discipline` = '".$discipline[0]."'
                AND `team_number` > -2
            ) `s` ON `d`.`score_id` = `s`.`id`
            INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
            WHERE `dcup_id` = '".$dcupId."'
              AND `p`.`sex` = '".$discipline[1]."'
            GROUP BY `person_id`
          ");
        }
        usort($persons, $compare);
        $position = 1;
        for ($i = 0; $i < count($persons); $i++) {
          $position = $i + 1;
          if ($i > 0 && $compare($persons[$i], $persons[$i-1]) === 0) {
            $position = $persons[$i-1]['position'];
          }
          $persons[$i]['position'] = $position;

          $db->insertRow("dcup_points".$u, array(
            'dcup_id' => $dcupId,
            'person_id' => $persons[$i]['person_id'],
            'points' => $persons[$i]['points'],
            'position' => $position,
            'discipline' => $discipline[0]
          ), false);
        }
      }
    }
  }

  public static function equal($a, $b) {
    for ($i = 0; $i < max(count($a['other']), count($b['other'])); $i++) { 
      if (!isset($a['other'][$i])) return false;
      elseif (!isset($b['other'][$i])) return false;
      elseif ($a['other'][$i] == $b['other'][$i]) continue;
      else return false;
    }
    return true;
  }

  public static function sortSingle($scores) {
    usort($scores, function ($a, $b) {
      if ($a['time'] == $b['time']) {
        for ($i=0; $i < max(count($a['other']), count($b['other'])); $i++) { 
          if (!isset($a['other'][$i])) return 1;
          elseif (!isset($b['other'][$i])) return -1;
          elseif ($a['other'][$i] == $b['other'][$i]) continue;
          else return ($a['other'][$i] < $b['other'][$i])? -1 : 1;
        }
      } else {
        return ($a['time'] < $b['time'])? -1 : 1;
      }
      return 0;
    });
    return $scores;
  }

  public static function getTeamScores($sex, $dcupId) {
    global $db;

    $rows = $db->getRows("
      SELECT `s`.`points`,`s`.`time`,
        `c`.`date`, `s`.`competition_id`,
        `c`.`event_id`, `c`.`event`,
        `c`.`place_id`, `c`.`place`,
        `s`.`team_id`, `s`.`team_number`, `t`.`short` AS `team`,
        `s`.`discipline`
      FROM `scores_dcup_team` `s`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
      INNER JOIN `teams` `t` ON `s`.`team_id` = `t`.`id`
      WHERE `s`.`sex` = '".$sex."'
      AND `s`.`dcup_id` = '".$dcupId."'
      ORDER BY `date`
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

    usort($teams, function($a, $b) {
      return $a->compare($b);
    });
    return array($teams, $competitions);
  }
}