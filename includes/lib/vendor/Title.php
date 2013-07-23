<?php

class Title
{
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
    }

    public static function get() {
        return self::$title;
    }
}