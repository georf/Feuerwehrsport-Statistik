<?php

class Map {
  public static function downloadStaticMap($type, $id) {
    global $config;

    $row = FSS::tableRow($type, $id);

    $lat = $row['lat'];
    $lon = $row['lon'];

    $sm = new StaticMapImage($lat, $lon, 8);
    return $sm->makeMap($config['base'].'styling/map/'.$type.'-'.$id.'.png');
  }

  public static function isFile($type, $id) {
    global $config;
    return is_file($config['base'].'styling/map/'.$type.'-'.$id.'.png');
  }

  public static function getImg($type, $id) {
    global $config;
    return '<img src="'.$config['url'].'styling/map/'.$type.'-'.$id.'.png" alt=""/>';
  }
}
