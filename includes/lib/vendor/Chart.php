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

    public static function img($type, $para = false, $big = true, $infochart = '') {

        $classes = array();
        $output = '<img src="/chart/'.$type;

        if (!is_array($para)) $para = array();

        foreach ($para as $p) {
            $output .= '.'.$p;
        }
        $output .= '.png" alt=""';

        if ($big) {
            $classes[] = 'big';
        }
        if (!empty($infochart)) {
            $classes[] = 'infochart';
            $output .= ' data-file="'.$infochart.'"';
        }

        if (count($classes) > 0) {
            $output .= ' class="'.implode(' ', $classes).'"';
        }

        return $output.'/>';
    }
}
