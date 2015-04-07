<?php

class CalculationTeam {
  public $disciplineTypes = array();

  public static function build($team) {
    return new self($team);
  }

  private $team;
  public function __construct($team) {
    $this->team = $team;

    foreach (FSS::$disciplines as $discipline) {
      if (FSS::isSingleDiscipline($discipline)) continue;
      foreach (FSS::$sexes as $sex) {
        $this->disciplineTypes[$discipline][$sex] = $this->getGroupScoreTypes($discipline, $sex);
      }
    }
  }

  public function getGroupScoreTypes($key, $sex) {
    global $db;

    return $db->getRows("
      SELECT `gst`.*
      FROM `group_scores` `gs` 
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `gs`.`team_id` = '".$this->team["id"]."'
      AND `gst`.`discipline` = '".$key."'
      AND `gs`.`sex` = '".$sex."'
      GROUP BY `gst`.`id`
      ORDER BY `gst`.`regular` DESC
    ");
  }

  public function getGroupScores($type, $sex) {
    global $db;

    $selects = array();
    $joins = array();
    for ($p = 1; $p <= WK::count($type['discipline']); $p++) {
      $selects[] = "`p".$p."`.`person_id` AS `person_".$p."`";
      $joins[] = "LEFT JOIN `person_participations` `p".$p."` ON `p".$p."`.`score_id` = `gs`.`id` AND `p".$p."`.`position` = ".$p;
    }

    return $db->getRows("
      SELECT `gs`.*,
        `gsc`.`competition_id`,
        `event_id`, `event`,
        `place_id`, `place`,
        `date`, '' AS `type`,
        ".implode(",", $selects)."
      FROM `group_scores` `gs` 
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `gsc`.`competition_id`
      ".implode(" ", $joins)."
      WHERE `gs`.`team_id` = '".$this->team["id"]."'
      AND `gsc`.`group_score_type_id` = '".$type['id']."'
      AND `gs`.`sex` = '".$sex."'"
    );
  }
}