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
            ENGINE = MYISAM DEFAULT CHARSET = utf8
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
            ENGINE = MYISAM DEFAULT CHARSET = utf8
            SELECT `s`.*, `p`.`name`, `p`.`firstname`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'female'
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
            ENGINE = MYISAM DEFAULT CHARSET = utf8
            SELECT `s`.*, `p`.`name`, `p`.`firstname`
            FROM `persons` `p`
            INNER JOIN `scores` `s` ON `s`.`person_id` = `p`.`id`
            WHERE `p`.`sex` = 'male'
        ",
    );

    private static $exists = array();

    public static function clean() {
        global $db;

        foreach (self::$tables as $table => $statement) {
            $db->query("DROP TABLE IF EXISTS `".$table."`");
        }

        self::$exists = array();
    }

    public static function generate($table) {
        global $db;

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
