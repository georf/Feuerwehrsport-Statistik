<?php

class CountStatistics {
  public static function persons() {
    global $db;
    return $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `persons`
      ", 'count');
  }

  public static function scores() {
    global $db;
    return $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores`
        WHERE `time` IS NOT NULL
      ", 'count');
  }

  public static function scores2() {
    global $db;
    return $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `scores`
        WHERE `time` IS NULL
      ", 'count');
  }

  public static function places() {
    global $db;
    return $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `places`
      ", 'count');
  }

  public static function events() {
    global $db;
    return $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `events`
      ", 'count');
  }

  public static function competitions() {
    global $db;
    return $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `competitions`
      ", 'count');
  }

  public static function teams() {
    global $db;
    return $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `teams`
      ", 'count');
  }
}