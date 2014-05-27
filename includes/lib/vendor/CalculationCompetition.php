<?php

class CalculationCompetition {
  public static function build($competition) {
    return new self($competition);
  }

  private $competition;
  public function __construct($competition) {
    $this->competition = $competition;
  }

  public function getDiscipline($key, $sex) {
    if (FSS::isGroupDiscipline($key)) {
      return $this->getGroupDiscipline($key, $sex);
    }
  }

  public function getGroupDiscipline($key, $sex) {
    global $db;

    $selects = array();
    $joins = array();
    for ($p = 1; $p <= WK::count($key); $p++) {
      $selects[] = "`p".$p."`.`id` AS `person_".$p."`,`p".$p."`.`name` AS `name".$p."`,`p".$p."`.`firstname` AS `firstname".$p."`";
      $joins[] = "LEFT JOIN `person_participations_".$key."` `pp".$p."` ON `pp".$p."`.`score_id` = `best`.`id` AND `pp".$p."`.`position` = ".$p;
      $joins[] = "LEFT JOIN `persons` `p".$p."` ON `pp".$p."`.`person_id` = `p".$p."`.`id`";
    }
    $sex = ($key == 'gs') ? '' : "AND `sex` = '".$sex."'";
    return $db->getRows("
      SELECT `best`.*,`t`.`name` AS `team`,`t`.`short` AS `shortteam`,
      ".implode(",", $selects)."
      FROM (
        SELECT *
        FROM (
          (
            SELECT `id`,`team_id`,`team_number`,`time`
            FROM `scores_".$key."`
            WHERE `time` IS NOT NULL
            ".$sex."
            AND `competition_id` = '".$this->competition['id']."'
          ) UNION (
            SELECT `id`,`team_id`,`team_number`,".FSS::INVALID." AS `time`
            FROM `scores_".$key."`
            WHERE `time` IS NULL
            ".$sex."
            AND `competition_id` = '".$this->competition['id']."'
          ) ORDER BY `time`
        ) `all`
        GROUP BY `team_id`,`team_number`
      ) `best`
      INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
      ".implode(" ", $joins)."
      ORDER BY `time`
    ");
  }

  public function missed() {
    global $db;

    return $db->getFirstRow("
      SELECT
        NOT EXISTS (
          SELECT 1 FROM `links` WHERE `for`='competition' AND `for_id`=".$this->competition['id']."
        ) AS `links`,
        EXISTS (
          SELECT 1 FROM (
            SELECT (
              SELECT COUNT(`id`) FROM `person_participations_la` WHERE score_id = s.id
            ) AS `count` FROM `scores_la` `s` WHERE `competition_id`=".$this->competition['id']."
          ) `i` WHERE `i`.`count` < 7
        ) AS `la_members`,
        EXISTS (
          SELECT 1 FROM (
            SELECT (
              SELECT COUNT(`id`) FROM `person_participations_fs` WHERE score_id = s.id
            ) AS `count` FROM `scores_fs` `s` WHERE `competition_id`=".$this->competition['id']."
          ) `i` WHERE `i`.`count` < 4
        ) AS `fs_members`,
        EXISTS (
          SELECT 1 FROM (
            SELECT (
              SELECT COUNT(`id`) FROM `person_participations_gs` WHERE score_id = s.id
            ) AS `count` FROM `scores_gs` `s` WHERE `competition_id`=".$this->competition['id']."
          ) `i` WHERE `i`.`count` < 6
        ) AS `gs_members`,
        EXISTS (
          SELECT 1 FROM `scores` WHERE `competition_id`=".$this->competition['id']." AND `team_id` IS NULL
        ) AS `team`,
        NOT EXISTS (
          SELECT 1 FROM `file_uploads` WHERE `competition_id`=".$this->competition['id']."
        ) as `files`
    ");
  }
  public $missedItem = array(
    "links" => "Verlinkungen",
    "la_members" => "Wettkämpfer beim Löschangriff",
    "fs_members" => "Wettkämpfer bei der Feuerwehrstafette",
    "gs_members" => "Wettkämpfer bei der Gruppenstafette",
    "team" => "Team-Zuordnungen bei Einzeldisziplinen",
    "files" => "Wettkampfergebnisse als Datei-Upload",
  );
}