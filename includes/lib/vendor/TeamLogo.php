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
}
