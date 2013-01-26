<?php

class Chart
{
    public static $factor = 1.5;

    public static function size($arg) {

        if (Check::get('big')) $arg *= self::$factor;

        return $arg;
    }


    public static function create($w, $h, $data) {
        $w = self::size($w);
        $h = self::size($h);
        return new pImage($w, $h, $data);
    }
}
