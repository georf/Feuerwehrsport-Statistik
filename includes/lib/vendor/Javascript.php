<?php

class Javascript {
  public static function scriptTag($path, $page) {
    $vz = opendir($path);
    while ($file = readdir($vz)) {
      if (is_file($path.$file) && $file == $page.'.js') {
        return '<script type="text/javascript" src="/'.$path.$file.'?version='.filectime($path.$file).'"></script>';
      }
    }
    closedir($vz);
    return '';
  }
}
