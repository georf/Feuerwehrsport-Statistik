<?php

class TeamLogo {
  public static function get($id, $logo) {
    global $config;
    if (!$logo || !is_file($config['logo-path'].$logo)) return '';
    if (!is_file($config['logo-path-mini'].$id.'.png')) {
      $imageOutput = new Imagick($config['logo-path'].$logo); // This will hold the resized image
      $imageOutput->cropThumbnailImage(24,24);
      $imageOutput->setImageFormat('png');
      $imageOutput->writeImage($config['logo-path-mini'].$id.'.png'); // Write it to disk
      $imageOutput->clear();
      $imageOutput->destroy();
    }
    return '<img src="/'.$config['logo-path-mini'].$id.'.png" alt=""/>';
  }
  public static function getTall($logo, $alt = "", $replacement = "") {
    global $config;
    if (!$logo || !is_file($config['logo-path'].$logo)) return $replacement;
    return '<img src="/'.$config['logo-path'].$logo.'" alt="'.htmlspecialchars($alt).'" title="'.htmlspecialchars($alt).'"/>';
  }

  private $id;
  private $logo;

  public static function build($team) {
    return new self($team['id'], $team['logo']);
  }

  public function __construct($id, $logo) {
    $this->id = $id;
    $this->logo = $logo;
  }

  public function remove() {
    global $db, $config;

    $db->updateRow('teams', $this->id, array(
      'logo' => NULL
    ));

    if ($this->exists()) {
      unlink($config['logo-path'].$this->logo);
    }
    if ($this->miniExists()) {
      unlink($config['logo-path-mini'].$this->id.'.png');
    }
    $logo = NULL;
  }

  public function path() {
    global $config;
    return $config['logo-path'].$this->logo;
  }

  public function miniPath() {
    global $config;
    return $config['logo-path-mini'].$this->id.'.png';
  }

  public function exists() {
    return is_file($this->path());
  }

  public function miniExists() {
    return is_file($this->miniPath());
  }
}
