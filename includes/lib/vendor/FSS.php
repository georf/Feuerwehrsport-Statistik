<?php

class FSS
{

    public static $disciplines = array('hl', 'hb', 'la', 'gs', 'fs');
    public static $disciplinesWithDoubleEvent = array('hl', 'hb', 'la', 'gs', 'fs', 'zk');
    public static $sexes = array('female', 'male');
    
    const INVALID = 99999999;

    private static $competitionTeamNumbers = array('female' => false, 'male' => false);
    private static $teamTeamNumbers = array('female' => false, 'male' => false);


    public static function isInvalid($time) {
        return ($time == self::INVALID || !$time);
    }


    public static function time($time) {
        if (self::isInvalid($time)) {
            return 'D';
        }

        return sprintf('%05.2f', intval($time)/100);
    }


    public static function teamNumber($number, $competition_id = false, $team_id = false, $cache = false, $sex = false, $pre = '') {
        global $db;

        if ($number == -1) {
            return $pre.'E';
        } elseif ($number <= -2 && $number >= -5) {
            return $pre.'F';
        } elseif ($number == -6) {
            return $pre.'A';
        }

        if (!$competition_id) {
            return (intval($number)+1);
        }

        if ($cache == 'competition') {
            if (self::$competitionTeamNumbers[$sex] === false) {
                TempDB::generate('x_team_numbers');
                $rows = $db->getRows("
                    SELECT `team_id`
                    FROM `x_team_numbers`
                    WHERE `competition_id` = '".$competition_id."'
                    AND `sex` = '".$sex."'
                ");

                self::$competitionTeamNumbers[$sex] = array();
                foreach ($rows as $row) {
                    self::$competitionTeamNumbers[$sex][$row['team_id']] = true;
                }
            }

            if (isset(self::$competitionTeamNumbers[$sex][$team_id])) {
                return $pre.(intval($number)+1);
            }
            return '';
        }

        TempDB::generate('x_team_numbers');
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

    public static function sexSymbol($sex) {
        return ($sex === 'female')? '♀' : '♂';
    }

    public static function palette($key) {
        switch ($key) {
            case 'female':
                return array("R"=>255,"G"=>96,"B"=>10,"Alpha"=>80);
            default:
                return array("R"=>32,"G"=>110,"B"=>255,"Alpha"=>80);
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
        if ($tall == 'middle') $n = 'middle-';
        elseif ($tall) $n = 'tall-';
        return '<img src="/styling/images/dis-'.$n.$key.'.png" alt="'.self::dis2name($key).'" title="'.self::dis2name($key).'"/>';
    }

    public static function isSingleDiscipline($key) {
        return in_array(strtolower($key), array('hl', 'hb'));
    }

    public static function isGroupDiscipline($key) {
        return !self::isSingleDiscipline($key);
    }

    public static function countNoEmpty($count) {
        return ($count == 0)? '' : $count;
    }

    public static function stateToText($short) {
        $states = array(
            'BW' => 'Baden-Württemberg',
            'BY' => 'Bayern',
            'BE' => 'Berlin',
            'BB' => 'Brandenburg',
            'HB' => 'Bremen',
            'HH' => 'Hamburg',
            'HE' => 'Hessen',
            'MV' => 'Mecklenburg-Vorpommern',
            'NI' => 'Niedersachsen',
            'NW' => 'Nordrhein-Westfalen',
            'RP' => 'Rheinland-Pfalz',
            'SL' => 'Saarland',
            'SN' => 'Sachsen',
            'ST' => 'Sachsen-Anhalt',
            'SH' => 'Schleswig-Holstein',
            'TH' => 'Thüringen',

            'CZ' => 'Tschechien',
            'DE' => 'Deutschland',
            'AT' => 'Österreich',
            'PL' => 'Polen',
        );

        if (isset($states[$short])) return $states[$short];
        else return $short;
    }

    public static function buildSexKey($key, $sex = null) {
        return $key.($sex? '-'.$sex : '');
    }

    public static function buildFullKey($key, $sex = null, $final = false) {
        return $key.'-'.($sex? $sex : '').'-'.($final !== false? $final : '');
    }

    public static function finalName($key) {
        $finals = array(
            -2 => "Finale",
            -3 => "Halbfinale",
            -4 => "Viertelfinale",
            -5 => "Achtelfinale",
        );
        return isset($finals[$key])? $finals[$key] : '';
    }

    public static function extractFullKey($fullKey) {
        if (preg_match('/^([a-z]+)-((?:fe)?male)?-(-\d)?$/', $fullKey, $result)) {
            return array(
                'key' => $result[1],
                'sex' => (isset($result[2])? $result[2] : null),
                'final' => (isset($result[3])? $result[3] : false),
            );
        }
        return false;
    }
}
