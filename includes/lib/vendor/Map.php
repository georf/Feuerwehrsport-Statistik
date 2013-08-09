<?php

class Map {
    public static function downloadStaticMap($type, $id) {
        global $config;

        $row = FSS::tableRow($type, $id);

        $lat = $row['lat'];
        $lon = $row['lon'];

        $url = 'http://ojw.dev.openstreetmap.org/StaticMap/?mode=API&show=1&lon='.$lon.'&lat='.$lat.'&z=8&mlon0='.$lon.'&mlat0='.$lat.'&w=500&h=400&fmt=png&att=logo';
        $file = file_get_contents($url);

        file_put_contents($config['base'].'styling/map/'.$type.'-'.$id.'.png', $file);
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
