<?php

class ResultFile {
  public static function getForCompetition($id) {
    global $db;

    $files = array();
    foreach ($db->getRows("
      SELECT *
      FROM `result_files`
      WHERE `competition_id` = '".$id."'
      ORDER BY `name`
    ") as $file) {
      $files[] = new self($file);
    }
    return $files;
  }

  public $name;
  public $sexKeys;

  public function __construct($properties) {
    $this->name = $properties["name"];
    $this->sexKeys = explode(',', $properties['keys']);
  }

  public function hasKey($sexKey) {
    return in_array($sexKey, $this->sexKeys);
  }
}