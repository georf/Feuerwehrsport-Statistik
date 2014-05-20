<?php

class CalculationTeam {
  public static function build($team) {
    return new self($team);
  }

  private $team;
  public function __construct($team) {
    $this->team = $team;
  }

  public function getGroupScores($key, $sex = false) {
    global $db;

    $selects = array();
    $joins = array();
    for ($p = 1; $p <= WK::count($key); $p++) {
      $selects[] = "`p".$p."`.`person_id` AS `person_".$p."`";
      $joins[] = "LEFT JOIN `person_participations_".$key."` `p".$p."` ON `p".$p."`.`score_id` = `s`.`id` AND `p".$p."`.`position` = ".$p;
    }

    $sex = ($sex) ? "AND `s`.`sex` = '".$sex."'" : '';

    return $db->getRows("
      SELECT `s`.*,
        `event_id`, `event`,
        `place_id`, `place`,
        `date`, '' AS `type`,
        ".implode(",", $selects)."
      FROM `scores_".$key."` `s`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
      ".implode(" ", $joins)."
      WHERE `s`.`team_id` = '".$this->team["id"]."'
      ".$sex
    );
  }
}