<?php

class Import {

  public static function getTeamNumber($team, $default = 1) {
    $team = trim($team);

    if (preg_match('/ 1$/', $team) || preg_match('/ I$/', $team)) {
      return 1;
    } elseif (preg_match('/ 2$/', $team) || preg_match('/ II$/', $team)) {
      return 2;
    } elseif (preg_match('/ 3$/', $team) || preg_match('/ III$/', $team)) {
      return 3;
    } elseif (preg_match('/ 4$/', $team) || preg_match('/ IV$/', $team)) {
      return 4;
    } elseif (preg_match('/ E$/', $team)) {
      return 0;
    }
    return $default;
  }

  public static function getTeamIds($team) {
    global $db;

    $team = trim($team);

    $likeTeam = preg_replace('/^FF /i', '', $team);
    $likeTeam = preg_replace('/^Team /i', '', $likeTeam);
    $likeTeam = preg_replace('/ I$/', '', $likeTeam);
    $likeTeam = preg_replace('/ II$/', '', $likeTeam);
    $likeTeam = preg_replace('/ III$/', '', $likeTeam);
    $likeTeam = preg_replace('/ IV$/', '', $likeTeam);
    $likeTeam = preg_replace('/ 1$/', '', $likeTeam);
    $likeTeam = preg_replace('/ 2$/', '', $likeTeam);
    $likeTeam = preg_replace('/ 3$/', '', $likeTeam);
    $likeTeam = preg_replace('/ 4$/', '', $likeTeam);
    $likeTeam = preg_replace('/ E$/', '', $likeTeam);

    $teamIds = $db->getRows("
      SELECT `id`
      FROM `teams`
      WHERE `name` LIKE '".$db->escape($likeTeam)."'
      OR `short` LIKE '".$db->escape($likeTeam)."'
    ", 'id');

    $teamSpellingIds = $db->getRows("
      SELECT `team_id` AS `id`
      FROM `teams_spelling`
      WHERE `name` LIKE '".$db->escape($likeTeam)."'
      OR `short` LIKE '".$db->escape($likeTeam)."'
    ", 'id');

    return array_unique(array_merge($teamIds, $teamSpellingIds));
  }

  public static function getTime($time) {
    $time = trim($time);

    if ($time == 'N') {
      return false;
    }

    if ($time == 'D' || $time == 'd') {
      return null;
    }

    if (!preg_match('|^[\d,:;.]+$|', $time)) {
      return false;
    }

    if (preg_match('|^(\d+):(\d{2})[:,](\d{2})$|', $time, $arr)) {
      $time = (intval($arr[1])*60+intval($arr[2])).':'.$arr[3];
    }

    if (strpos($time, ',') !== false || strpos($time, '.') !== false) {
      $time = str_replace(',','.',$time);
      $time = str_replace(':','',$time);
      $time = str_replace(';','',$time);
      $time = floatval($time) *100;
    } elseif (strpos($time, ';') !== false || strpos($time, ':')) {
      $time = str_replace(';','.',$time);
      $time = str_replace(':','.',$time);
      $time = floatval($time) *100;
    } elseif (is_numeric($time)) {
      $time = intval($time);

      if ($time > 98) return false;
      else $time *= 100;
    }


    if (is_numeric($time) && $time < 1200) {
      return null;
    }

    if (is_numeric($time) && $time > 99800) {
      return null;
    }

    return $time;
  }

  public static function getCorrectClass($correct) {
    return ($correct)?'correct':'notcorrect';
  }

  public static function getPerson($name, $firstname, $sex) {
    global $db;
    $result_search = $db->getFirstRow("
      SELECT *
      FROM `persons`
      WHERE `name` LIKE '".$db->escape($name)."'
      AND `firstname` LIKE '".$db->escape($firstname)."'
      AND `sex` = '".$db->escape($sex)."'");

    if (!$result_search) {
      $result_search = $db->getFirstRow("
        SELECT `p`.`name`,`p`.`firstname`
        FROM `persons_spelling` `s`
        INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
        WHERE `s`.`name` = '".$db->escape($name)."'
        AND `s`.`firstname` = '".$db->escape($firstname)."'
        AND `s`.`sex` = '".$db->escape($sex)."'");
    }
    return $result_search;
  }

  public static function getOtherOfficialNames($personId) {
    global $db;

    return $db->getRows("
      SELECT CONCAT(`firstname`, ' ', `name`) AS `full_name`
      FROM `persons_spelling`
      WHERE `person_id` = '".$db->escape($personId)."'
      AND `official` = 1
    ", 'full_name');
  }
}
