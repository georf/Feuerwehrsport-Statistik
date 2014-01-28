<?php

class Check
{
    public static function post()
    {
        $arguments = func_get_args();
        for ($i = 0, $l = count($arguments); $i < $l; $i++) {
            if (!isset($_POST[$arguments[$i]])) {
                return false;
            }
            if ($_POST[$arguments[$i]] === '') {
                return false;
            }
        }
        return true;
    }

    public static function get()
    {
        $arguments = func_get_args();
        for ($i = 0, $l = count($arguments); $i < $l; $i++) {
            if (!isset($_GET[$arguments[$i]])) {
                return false;
            }
            if ($_GET[$arguments[$i]] === '') {
                return false;
            }
        }
        return true;
    }

    public static function date()
    {
        $arguments = func_get_args();
        for ($i = 0, $l = count($arguments); $i < $l; $i++) {
            if (!preg_match('|^[0-9]{4}-[0-9]{2}-[0-9]{2}$|', $arguments[$i])) {
                return false;
            }
        }
        return true;
    }

    public static function isIn($id, $table)
    {
        global $db;

        if (!is_numeric($id)) {
            return false;
        }

        $result = $db->getFirstRow("SELECT `id` FROM `".$table."` WHERE `id` = '".$db->escape($id)."' LIMIT 1;");

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function isAdmin() {
      return isset($_SESSION['loggedin']);
    }

    public static function isSex($sex) {
        return in_array($sex, array('male', 'female'));
    }

    public static function isDiscipline($discipline) {
        return in_array($discipline, array('hl', 'hb', 'la', 'gs', 'fs'));
    }

    public static function isDate($date) {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    }
}
