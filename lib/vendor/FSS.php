<?php

class FSS
{
    const INVALID = 99999999;


    public static function isInvalid($time) {
        return ($time == self::INVALID || !$time);
    }


    public static function time($time) {
        if (self::isInvalid($time)) {
            return 'D';
        }

        return sprintf('%05.2f', intval($time)/100);
    }


    public static function teamNumber($number) {
        if ($number == -1) {
            return 'E';
        } elseif ($number == -2) {
            return 'F';
        }
        return (intval($number)+1);
    }


    public static function teamNumberLong($number) {
        if ($number == -1) {
            return 'Einzelstarter';
        } elseif ($number == -2) {
            return 'Finale';
        }
        return 'Mannschaft '.strval(intval($number)+1);
    }


    public static function sex($sex) {
        return ($sex === 'female')? 'weiblich':'männlich';
    }


    public static function tableRow($table, $id) {
        global $db;

        return $db->getFirstRow("
            SELECT *
            FROM `".$table."`
            WHERE `id` = '".$db->escape($id)."'
            LIMIT 1;
        ");
    }

    public static function competition($id) {
        global $db;

        return $db->getFirstRow("
            SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`,
                `t`.`persons`,`t`.`run`,`t`.`score`,`t`.`id` AS `score_type`
            FROM `competitions` `c`
            INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
            INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
            LEFT JOIN `score_types` `t` ON `t`.`id` = `c`.`score_type_id`
            WHERE `c`.`id` = '".$db->escape($id)."'
            LIMIT 1;
        ");
    }


    public static function laType($key) {
        global $config;
        return (isset($config['la'][$key])) ?  $config['la'][$key] : '';
    }


    public static function fsType($key) {
        global $config;
        return (isset($config['fs'][$key])) ?  $config['fs'][$key] : '';
    }


    public static function name2id($name) {
        return preg_replace('|[^a-z0-9]|', '', strtolower($name));
    }


    public static function dis2name($key) {
        $c = array(
            'hl' => 'Hakenleitersteigen',
            'la' => 'Löschangriff',
            'gs' => 'Gruppenstafette',
            'fs' => 'Feuerwehrstafette',
            'zk' => 'Zweikampf',
            'hb' => 'Hindernisbahn',
        );
        return $c[$key];
    }
}
