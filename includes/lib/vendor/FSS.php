<?php

class FSS
{
    const INVALID = 99999999;

    private static $competitionTeamNumbers = false;
    private static $teamTeamNumbers = false;


    public static function isInvalid($time) {
        return ($time == self::INVALID || !$time);
    }


    public static function time($time) {
        if (self::isInvalid($time)) {
            return 'D';
        }

        return sprintf('%05.2f', intval($time)/100);
    }


    public static function teamNumber($number, $competition_id = false, $team_id = false, $cache = false, $pre = '') {
        global $db;

        if ($number == -1) {
            return $pre.'E';
        } elseif ($number == -2) {
            return $pre.'F';
        }

        if (!$competition_id) {
            return (intval($number)+1);
        }

        if ($cache == 'competition') {
            if (self::$competitionTeamNumbers === false) {
                $rows = $db->getRows("
                    SELECT `team_id`
                    FROM `x_team_numbers`
                    WHERE `competition_id` = '".$competition_id."'
                ");

                self::$competitionTeamNumbers = array();
                foreach ($rows as $row) {
                    self::$competitionTeamNumbers[$row['team_id']] = true;
                }
            }

            if (isset(self::$competitionTeamNumbers[$team_id])) {
                return $pre.(intval($number)+1);
            }
            return '';
        }

        if ($cache == 'team') {
            if (self::$teamTeamNumbers === false) {
                $rows = $db->getRows("
                    SELECT `team_id`
                    FROM `x_team_numbers`
                    WHERE `competition_id` = '".$competition_id."'
                ");

                self::$teamTeamNumbers = array();
                foreach ($rows as $row) {
                    self::$teamTeamNumbers[$row['team_id']] = true;
                }
            }

            if (isset(self::$teamTeamNumbers[$team_id])) {
                return $pre.(intval($number)+1);
            }
            return '';
        }

        if (count($db->getRows("
            SELECT `team_id`
            FROM `x_team_numbers`
            WHERE `competition_id` = '".$competition_id."'
            AND `team_id` = '".$team_id."'
        "))) {
            return $pre.(intval($number)+1);
        }
        return '';
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

    public static function palette($sex) {
        if ($sex === 'female') {
            return array("R"=>229,"G"=>11,"B"=>11,"Alpha"=>80);
        } else {
            return array("R"=>0,"G"=>113,"B"=>222,"Alpha"=>100);
        }
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

        TempDB::generate('x_full_competitions');

        return $db->getFirstRow("
            SELECT *
            FROM `x_full_competitions`
            WHERE `id` = '".$db->escape($id)."'
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
        if (isset($c[$key])) return $c[$key];
        return '';
    }

    public static function dis2img($key, $tall = false) {
        $n = '';
        if ($tall == 'blue') $n = 'blue-';
        elseif ($tall) $n = 'tall-';
        return '<img src="/styling/images/dis-'.$n.$key.'.png" alt="'.self::dis2name($key).'" title="'.self::dis2name($key).'"/>';
    }

    public static function countNoEmpty($count) {
        return ($count == 0)? '' : $count;
    }
}
