<?php

class WK {
  private static $disciplines = array(
    'la' => array(
      'Maschinist',
      'A-Länge',
      'Saugkorb',
      'B-Schlauch',
      'Strahlrohr links',
      'Verteiler',
      'Strahlrohr rechts'
    ),
    'gs' => array(
      'B-Schlauch',
      'Verteiler',
      'C-Schlauch',
      'Knoten',
      'D-Schlauch',
      'Läufer'
    ),
    'fs' => array(
      array('female' => 'Leiterwand', 'male' => 'Haus'),
      array('female' => 'Hürde', 'male' => 'Wand'),
      'Balken',
      'Feuer'
    )
  );

  public static function type($wk, $sex, $key) {
    return self::get($wk, $sex, self::$disciplines[$key]);
  }

  public static function count($key) {
    return count(self::$disciplines[$key]);
  }

  private static function get($pos, $sex, $wks) {
    if (isset($wks[$pos-1])) {
      $wk = $wks[$pos-1];
      return (is_array($wk)) ? $wk[$sex] : $wk;
    } else {
      return '';
    }
  }
}
