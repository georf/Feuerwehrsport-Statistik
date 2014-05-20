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
      $joins[] = "LEFT JOIN `person_participations_".$key."` `p".$p."` ON `p".$p."`.`score_id` = `s`.`id` AND `p".$p."`.`position` = ".$p;
    }

    return $db->getRows("
      SELECT
        `c`.`place_id`,`c`.`place`,
        `c`.`event_id`,`c`.`event`,
        `c`.`score_type_id`,
        `s`.`competition_id`,`c`.`date`,
        `s`.`time`,`s`.`team_id`,
        `s`.`id` AS `score_id`,`s`.`team_number`,
        ".implode(",", $selects)."
      FROM `scores_".$key."` `s`
      INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
      INNER JOIN `person_participations_".$key."` `p` ON `p`.`score_id` = `s`.`id` AND `p`.`person_id` = '".$this->person['id']."'
      ".implode(" ", $joins)
    );
  }
}