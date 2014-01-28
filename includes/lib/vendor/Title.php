<?php

class Title {
  private static $title = false;


  public static function replace($header, $title = '') {
    if (is_string(self::$title)) {
      $title = self::$title.' - Feuerwehrsport-Statistik';
    }
    return str_replace('{[PAGE_TITLE]}', $title, $header);
  }

  public static function set($title) {
    if (is_string($title) && !is_string(self::$title)) {
      self::$title = $title;
    }

    return '<h1>'.$title.'</h1>';
  }

  public static function get() {
    return self::$title;
  }

  public static function name($key) {
    $names = array(
      'place'            => 'Ort',
      'event'            => 'Typ',
      'bestScoresOfYear' => 'Bestzeiten des Jahres',
      'year'             => 'Jahr',
    );

    return $names[$key];
  }
}
