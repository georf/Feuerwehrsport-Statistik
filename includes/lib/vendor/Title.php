<?php

class Title {
  private static $title = false;


  public static function replace($header, $title = '') {
    if (is_string(self::$title)) {
      $title = self::$title.' - Feuerwehrsport-Statistik';
    }
    return str_replace('{[PAGE_TITLE]}', $title, $header);
  }

  public static function set($title, $subTitle = false) {
    if (is_string($title) && !is_string(self::$title)) {
      self::$title = $title;
    }

    return self::h1($title, $subTitle);
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

  public static function h1($title, $subTitle = false) {
    $subTitleLine = '';
    if ($subTitle) $subTitleLine = '<span>'.htmlspecialchars($subTitle).'</span>';
    return '<div class="page-header">'.$subTitleLine.'<h1>'.htmlspecialchars($title).'</h1></div>';
  }

  public static function h2($title, $anchor = false) {
    $anchor = ($anchor)? ' id="'.$anchor.'"' : '';
    return '<h2'.$anchor.'>'.htmlspecialchars($title).'</h2>';
  }
}
