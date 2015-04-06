<?php

class CalculationPerson {
  public static function build($person) {
    return new self($person);
  }

  private $person;
  public function __construct($person) {
    $this->person = $person;
  }

  public function getGroupScores($key) {
    global $db;

    $selects = array();
    $joins = array();
    for ($p = 1; $p <= WK::count($key); $p++) {
      $selects[] = "`p".$p."`.`person_id` AS `person_".$p."`";
      $joins[] = "LEFT JOIN `person_participations` `p".$p."` ON `p".$p."`.`score_id` = `gs`.`id` AND `p".$p."`.`position` = ".$p;
    }

    return $db->getRows("
      SELECT
        `c`.`place_id`,`c`.`place`,
        `c`.`event_id`,`c`.`event`,
        `c`.`score_type_id`,
        `gsc`.`competition_id`,`c`.`date`,
        `gs`.`time`,`gs`.`team_id`,
        `gs`.`id` AS `score_id`,`gs`.`team_number`,
        ".implode(",", $selects)."
      FROM `group_scores` `gs` 
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `gsc`.`competition_id`
      INNER JOIN `person_participations` `p` ON `p`.`score_id` = `gs`.`id` AND `p`.`person_id` = '".$this->person['id']."'
      ".implode(" ", $joins)."
      WHERE `gst`.`discipline` = '".$key."'"
    );
  }
}