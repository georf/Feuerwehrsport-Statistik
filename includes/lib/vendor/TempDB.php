<?php

class TempDB {

    private static $tables = array(
        'x_full_competitions' => "
            CREATE TABLE x_full_competitions
            (
                UNIQUE KEY `id` (`id`),
                KEY `place_id` (`place_id`),
                KEY `event_id` (`event_id`),
                KEY `date` (`date`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`,
                `t`.`persons`,`t`.`run`,`t`.`score`,`t`.`id` AS `score_type`
            FROM `competitions` `c`
            INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
            INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
            LEFT JOIN `score_types` `t` ON `t`.`id` = `c`.`score_type_id`
        ",
        'x_scores_female' => "
            CREATE TABLE x_scores_female
            (
                UNIQUE KEY `id` (`id`),
                KEY `person_id` (`person_id`),
                KEY `competition_id` (`competition_id`),
                KEY `team_id` (`team_id`),
                KEY `time` (`time`),
                KEY `discipline` (`discipline`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT `s`.*, `p`.`name`, `p`.`firstname`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'female'
        ",
        'x_scores_hbf' => "
            CREATE TABLE x_scores_hbf
            (
                UNIQUE KEY `id` (`id`),
                KEY `person_id` (`person_id`),
                KEY `competition_id` (`competition_id`),
                KEY `team_id` (`team_id`),
                KEY `time` (`time`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT `s`.*
            FROM `scores` `s`
            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'female'
            AND `s`.`discipline` = 'HB'
        ",
        'x_scores_hbm' => "
            CREATE TABLE x_scores_hbm
            (
                UNIQUE KEY `id` (`id`),
                KEY `person_id` (`person_id`),
                KEY `competition_id` (`competition_id`),
                KEY `team_id` (`team_id`),
                KEY `time` (`time`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT `s`.*
            FROM `scores` `s`
            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'male'
            AND `s`.`discipline` = 'HB'
        ",
        'x_scores_hlf' => "
            CREATE TABLE x_scores_hlf
            (
                UNIQUE KEY `id` (`id`),
                KEY `person_id` (`person_id`),
                KEY `competition_id` (`competition_id`),
                KEY `team_id` (`team_id`),
                KEY `time` (`time`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT `s`.*
            FROM `scores` `s`
            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'female'
            AND `s`.`discipline` = 'HL'
        ",
        'x_scores_hlm' => "
            CREATE TABLE x_scores_hlm
            (
                UNIQUE KEY `id` (`id`),
                KEY `person_id` (`person_id`),
                KEY `competition_id` (`competition_id`),
                KEY `team_id` (`team_id`),
                KEY `time` (`time`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT `s`.*
            FROM `scores` `s`
            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'male'
            AND `s`.`discipline` = 'HL'
        ",
        'x_scores_hl' => "
            CREATE TABLE x_scores_hl
            (
                UNIQUE KEY `id` (`id`),
                KEY `person_id` (`person_id`),
                KEY `competition_id` (`competition_id`),
                KEY `team_id` (`team_id`),
                KEY `time` (`time`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT `s`.*
            FROM `scores` `s`
            INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'male'
            AND `s`.`discipline` = 'HL'
        ",
        'x_scores_male' => "
            CREATE TABLE x_scores_male
            (
                UNIQUE KEY `id` (`id`),
                KEY `person_id` (`person_id`),
                KEY `competition_id` (`competition_id`),
                KEY `team_id` (`team_id`),
                KEY `time` (`time`),
                KEY `discipline` (`discipline`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT `s`.*, `p`.`name`, `p`.`firstname`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'male'
        ",
        'x_team_numbers' => "
            CREATE TABLE x_team_numbers
            (
                KEY `competition_id` (`competition_id`),
                KEY `team_id` (`team_id`),
                KEY `sex` (`sex`)
            )
            ENGINE = INNODB DEFAULT CHARSET = utf8
            SELECT *
            FROM (
                    SELECT `competition_id` , `team_id` , `team_number`, `sex`
                    FROM `scores` `s`
                    INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
                    WHERE `team_id` IS NOT NULL
                    AND `team_number` > 0
                UNION
                    SELECT `competition_id` , `team_id` , `team_number`, `sex`
                    FROM `scores_la`
                    WHERE `team_number` > 0
                UNION
                    SELECT `competition_id` , `team_id` , `team_number`, 'female' AS `sex`
                    FROM `scores_gs`
                    WHERE `team_number` > 0
                UNION
                    SELECT `competition_id` , `team_id` , `team_number`, `sex`
                    FROM `scores_fs`
                    WHERE `team_number` > 0
            ) `rows`
            GROUP BY `competition_id` , `team_id` , `team_number`, `sex`
        ",
    );

    private static $exists = array();

    public static function clean() {
        global $db;


        $tables = $db->getRows("SHOW TABLES");
        foreach ($tables as $table) {
            foreach ($table as $t) {
                if (strpos($t, 'x_') === 0) {
                    $db->query("DROP TABLE IF EXISTS `".$t."`");
                }
            }
        }
        self::$exists = array();
    }

    public static function generate($table = false) {
        global $db;

        if ($table === false) {
            foreach (self::$tables as $key => $value) {
                self::generate($key);
            }
            return;
        }

        if (!array_key_exists($table, self::$tables)) return false;

        if (isset(self::$exists[$table]) || count($db->getRows("SHOW TABLES LIKE '".$table."'"))) {
            self::$exists[$table] = true;
            return true;
        } else {
            self::$exists[$table] = true;

            return $db->query(self::$tables[$table]);
        }
    }
}
