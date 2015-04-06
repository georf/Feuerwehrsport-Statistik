<?php

class CalculationCompetition {  
  public static function build($competition) {
    return new self($competition);
  }

  private $competition;
  private $resultFiles;
  private $scores;

  public function __construct($competition) {
    $this->competition = $competition;
  }

  public function disciplines() {
    return array(
     $this->discipline('hb', 'female', false, 'female'),
     $this->discipline('hb', 'female', -2, 'female'),
     $this->discipline('hb', 'female', -3, 'female'),
     $this->discipline('hb', 'female', -4, 'female'),
     $this->discipline('hb', 'female', -5, 'female'),
     $this->discipline('hb', 'male', false, 'male'),
     $this->discipline('hb', 'male', -2, 'male'),
     $this->discipline('hb', 'male', -3, 'male'),
     $this->discipline('hb', 'male', -4, 'male'),
     $this->discipline('hb', 'male', -5, 'male'),
     $this->discipline('hl', 'female', false, 'female'),
     $this->discipline('hl', 'female', -2, 'female'),
     $this->discipline('hl', 'female', -3, 'female'),
     $this->discipline('hl', 'female', -4, 'female'),
     $this->discipline('hl', 'female', -5, 'female'),
     $this->discipline('hl', 'male', false, 'male'),
     $this->discipline('hl', 'male', -2, 'male'),
     $this->discipline('hl', 'male', -3, 'male'),
     $this->discipline('hl', 'male', -4, 'male'),
     $this->discipline('hl', 'male', -5, 'male'),
     $this->discipline('zk', 'female', false, 'female'),
     $this->discipline('zk', 'male', false, 'male'),
     $this->discipline('gs', null, false, 'female'),
     $this->discipline('fs', 'female', false, 'female'),
     $this->discipline('fs', 'male', false, 'male'),
     $this->discipline('la', 'female', false, 'female'),
     $this->discipline('la', 'male', false, 'male'),
    );
  }

  public function discipline($key, $sex, $final, $origSex) {
    return array(
      'key' => $key,
      'sex' => $sex,
      'final' => $final,
      'sexKey' => FSS::buildSexKey($key, $sex),
      'fullKey' => FSS::buildFullKey($key, $sex, $final),
      'origSex' => $origSex,
    );
  }

  public function disciplineName($key, $sex = null, $final = false, $short = false, $team = false) {
    if (is_array($key)) {
      return $this->disciplineName($key['key'], $key['sex'], $key['final'], $sex, $final);
    }

    if ($team) {
      return ($short?strtoupper($key):FSS::dis2name($key)).($sex?' '.FSS::sex($sex):'').' - Mannschaft'.($short?'':'swertung');
    } elseif ($final) {
      return ($short?strtoupper($key):FSS::dis2name($key)).($sex?' '.FSS::sex($sex):'').' - '.FSS::finalName($final);
    } else {
      return FSS::dis2name($key).($sex?' '.FSS::sex($sex):'');
    }
  }

  public function countSingleScores() {
    return 
      $this->count('hb', 'female') +
      $this->count('hb', 'male') +
      $this->count('hl', 'female') +
      $this->count('hl', 'male');
  }

  public function getResultFiles($sexKey = false) {
    if ($this->resultFiles === null) {
      $this->resultFiles = ResultFile::getForCompetition($this->competition['id']);
    }

    if (!$sexKey) return $this->resultFiles;

    $currentFiles = array();
    foreach ($this->resultFiles as $file) {
      if ($file->hasKey($sexKey)) $currentFiles[] = $file;
    }
    return $currentFiles;
  }

  public function c($key, $sex = null, $final = false) {
    return FSS::countNoEmpty(self::count($key, $sex, $final));
  }

  public function count($key, $sex = null, $final = false) {
    return count($this->scores($key, $sex, $final));
  }

  public function exists($key, $final = false) {
    return $this->count($key, 'female', $final) || $this->count($key, 'male', $final);
  }

  public function countWithSex($key, $final = false) {
    $female = $this->count($key, 'female', $final);
    $male = $this->count($key, 'male', $final);
    $output = array();
    $team = FSS::isGroupDiscipline($key) ? "mannschaften" : ""; 
    if ($female) $output[] = $female." Frauen".$team;
    if ($male) $output[] = $male." Männer".$team;
    return implode(" und ", $output);
  }

  public function scores($key, $sex = null, $final = false) {
    if (is_array($key)) {
      return $this->scores($key['key'], $key['sex'], $key['final']);
    }

    $fullKey = FSS::buildFullKey($key, $sex, $final);
    if (!isset($this->scores[$fullKey])) {
      $this->scores[$fullKey] = $this->getDiscipline($key, $sex, $final);
    }
    return $this->scores[$fullKey];
  }

  public function getDiscipline($key, $sex, $final = false) {
    if ($key == 'zk') {
      return $this->getDoubleEvent($sex);
    } elseif (FSS::isGroupDiscipline($key)) {
      return $this->getGroupDiscipline($key, $sex);
    } else {
      return $this->getSingleDiscipline($key, $sex, $final);
    }
  }

  public function getDoubleEvent($sex) {
    global $db;

    return $db->getRows("
      SELECT
        0 AS `id`,
        `hl`.`person_id`,`p`.`name` AS `name`,`p`.`firstname` AS `firstname`,
        `hb`.`time` AS `hb`,
        `hl`.`time` AS `hl`,
        `hb`.`time` + `hl`.`time` AS `time`
      FROM (
        SELECT `person_id`,`time`
        FROM `scores`
        WHERE `time` IS NOT NULL
        AND `competition_id` = '".$this->competition['id']."'
        AND `discipline` = 'HL'
        AND `team_number` > -2
        ORDER BY `time`
      ) `hl`
      INNER JOIN (
        SELECT `person_id`,`time`
        FROM `scores`
        WHERE `time` IS NOT NULL
        AND `competition_id` = '".$this->competition['id']."'
        AND `discipline` = 'HB'
        AND `team_number` > -2
        ORDER BY `time`
      ) `hb` ON `hl`.`person_id` = `hb`.`person_id`
      INNER JOIN `persons` `p` ON `hb`.`person_id` = `p`.`id` AND `p`.`sex` = '".$sex."'
      GROUP BY `p`.`id`
      ORDER BY `time`
    ");
  }

  public function getSingleDiscipline($key, $sex, $final) {
    global $db;

    if ($final !== false) {
      $whereFinal = "=".$final;
    } else {
      $whereFinal = "> -2";
    }

    return $db->getRows("
      SELECT `best`.*,
        `t`.`name` AS `team`,`t`.`short` AS `shortteam`,
        `p`.`name` AS `name`,`p`.`firstname` AS `firstname`
      FROM (
        SELECT *
        FROM (
          (
            SELECT `id`,`team_id`,`team_number`,
            `person_id`,
            `time`
            FROM `scores`
            WHERE `time` IS NOT NULL
            AND `competition_id` = '".$this->competition['id']."'
            AND `discipline` = '".$key."'
            AND `team_number` ".$whereFinal."
          ) UNION (
            SELECT `id`,`team_id`,`team_number`,
            `person_id`,
            ".FSS::INVALID." AS `time`
            FROM `scores`
            WHERE `time` IS NULL
            AND `competition_id` = '".$this->competition['id']."'
            AND `discipline` = '".$key."'
            AND `team_number` ".$whereFinal."
          ) ORDER BY `time`
        ) `all`
        GROUP BY `person_id`
      ) `best`
      LEFT JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
      INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
      ".($sex? " WHERE `sex` = '".$sex."' " : "")."
      ORDER BY `time`
    ");
  }


  public function getGroupDiscipline($key, $sex) {
    global $db;

    if ($key == 'gs' && $sex == 'male') return array();

    $selects = array();
    $joins = array();
    for ($p = 1; $p <= WK::count($key); $p++) {
      $selects[] = "`p".$p."`.`id` AS `person_".$p."`,`p".$p."`.`name` AS `name".$p."`,`p".$p."`.`firstname` AS `firstname".$p."`";
      $joins[] = "LEFT JOIN `person_participations` `pp".$p."` ON `pp".$p."`.`score_id` = `best`.`id` AND `pp".$p."`.`position` = ".$p;
      $joins[] = "LEFT JOIN `persons` `p".$p."` ON `pp".$p."`.`person_id` = `p".$p."`.`id`";
    }
    return $db->getRows("
      SELECT `best`.*,`t`.`name` AS `team`,`t`.`short` AS `shortteam`,
      ".implode(",", $selects)."
      FROM (
        SELECT `all`.`id`,`team_id`,`team_number`,`time`,`run`
        FROM (
          (
            SELECT `id`,`team_id`,`team_number`,`time`,`run`,`group_score_category_id`
            FROM `group_scores`
            WHERE `time` IS NOT NULL
            AND `sex` = '".$sex."'
          ) UNION (
            SELECT `id`,`team_id`,`team_number`,".FSS::INVALID." AS `time`,`run`,`group_score_category_id`
            FROM `group_scores`
            WHERE `time` IS NULL
            AND `sex` = '".$sex."'
          ) ORDER BY `time`
        ) `all`
        INNER JOIN `group_score_categories` `gsc` ON `all`.`group_score_category_id` = `gsc`.`id`
        INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
        WHERE `gst`.`discipline` = '".$key."'
        AND `gsc`.`competition_id` = '".$this->competition['id']."'
        GROUP BY `team_id`,`team_number`,`run`
      ) `best`
      INNER JOIN `teams` `t` ON `t`.`id` = `best`.`team_id`
      ".implode(" ", $joins)."
      ORDER BY `time`
    ");
  }

  public function mapInformation() {
    global $db;
    return array(
      'place' => FSS::tableRow('places', $this->competition['place_id']),
      'teams' => $db->getRows("
        SELECT t.name, t.id, t.lat, t.lon
        FROM (
          SELECT `team_id`
          FROM `scores`
          WHERE `competition_id` = '".$this->competition['id']."'
        UNION
          SELECT `team_id`
          FROM `group_scores` `gs` 
          INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
          WHERE `competition_id` = '".$this->competition['id']."'
        ) s
        INNER JOIN `teams` `t` ON `s`.`team_id` = `t`.`id`
        WHERE `t`.`lat` IS NOT NULL AND `t`.`lon` IS NOT NULL")
    );
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
              SELECT COUNT(`id`) 
              FROM `person_participations`
              WHERE score_id = gs.id
            ) AS `count`
            FROM `group_scores` `gs` 
            INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
            INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
            WHERE `gsc`.`competition_id`= '".$this->competition['id']."'
            AND `gst`.`discipline` = 'LA'
          ) `i`
          WHERE `i`.`count` < 7
        ) AS `la_members`,
        EXISTS (
          SELECT 1 FROM (
            SELECT (
              SELECT COUNT(`id`) 
              FROM `person_participations`
              WHERE score_id = gs.id
            ) AS `count`
            FROM `group_scores` `gs` 
            INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
            INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
            WHERE `gsc`.`competition_id`= '".$this->competition['id']."'
            AND `gst`.`discipline` = 'FS'
          ) `i`
          WHERE `i`.`count` < 4
        ) AS `fs_members`,
        EXISTS (
          SELECT 1 FROM (
            SELECT (
              SELECT COUNT(`id`) 
              FROM `person_participations`
              WHERE score_id = gs.id
            ) AS `count`
            FROM `group_scores` `gs` 
            INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
            INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
            WHERE `gsc`.`competition_id`= '".$this->competition['id']."'
            AND `gst`.`discipline` = 'GS'
          ) `i`
          WHERE `i`.`count` < 6
        ) AS `gs_members`,
        EXISTS (
          SELECT 1 FROM `scores` WHERE `competition_id`=".$this->competition['id']." AND `team_id` IS NULL
        ) AS `team`,
        NOT EXISTS (
          SELECT 1 FROM `result_files` WHERE `competition_id`=".$this->competition['id']."
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