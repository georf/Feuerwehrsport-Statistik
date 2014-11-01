#!/usr/bin/php
<?php (PHP_SAPI === 'cli') || exit();

$options = getopt("fc", array('file:'));
$force = isset($options["f"]);
$compress = isset($options["c"]);
$fileToForce = isset($options["file"]) ? $options["file"] : false ;

$coffeePath = __DIR__."/coffeescripts/";
$javascriptPath = __DIR__."/javascripts/";
$command = __DIR__.'/coffeescript-concat/coffeescript-concat -I %s/classes/ %s | coffee -s -p > %s';
$command2 = 'ccjs %s > %s';
$destinationPath = __DIR__."/../js/";

$subPaths = array('', 'pages/', 'administration/');
$javascriptPaths = array('');

foreach ($subPaths as $subPath) {
  $vz = opendir($coffeePath.$subPath);
  while ($file = readdir($vz)) {
    if (is_file($coffeePath.$subPath.$file) && preg_match('|\.coffee$|', $file)) {
      $destination = $destinationPath.$subPath.preg_replace('|\.coffee$|', '.js', $file);

      if (is_file($destination)) {
        $timeCoffee = filemtime($coffeePath.$subPath.$file);
        $timeJavascript = filemtime($destination);
        if (!$force && $timeCoffee < $timeJavascript && $fileToForce != $file) continue;
      }

      echo "=================================================================\n";
      echo $file."\n";
      echo "=================================================================\n";

      do $tempFile = '/tmp/'.rand(); while(is_file($tempFile));
      do $tempFile2 = '/tmp/'.rand(); while(is_file($tempFile2));

      shell_exec(sprintf($command, $coffeePath, $coffeePath.$subPath.$file, $tempFile));

      if ($compress) {
        shell_exec(sprintf($command2, $tempFile, $tempFile2));
      } else {
        copy($tempFile, $tempFile2);
      }

      copy($tempFile2, $destination);
      unlink($tempFile);
      unlink($tempFile2);
    }
  }
  closedir($vz);
}


foreach ($javascriptPaths as $jPath) {
  $vz = opendir($javascriptPath.$jPath);
  while ($file = readdir($vz)) {
    if (is_file($javascriptPath.$jPath.$file) && preg_match('|\.js$|', $file)) {
      $destination = $destinationPath.$jPath.preg_replace('|\.js$|', '.js', $file);

      if (is_file($destination)) {
        $timeJavascript = filemtime($javascriptPath.$jPath.$file);
        $timeBuild = filemtime($destination);
        if (!$force && $timeJavascript < $timeBuild) continue;
      }

      echo "=================================================================\n";
      echo $file."\n";
      echo "=================================================================\n";

      do $tempFile = '/tmp/'.rand(); while(is_file($tempFile));

      if ($compress) {
        shell_exec(sprintf($command2, $javascriptPath.$jPath.$file, $tempFile));
      } else {
        copy($javascriptPath.$jPath.$file, $tempFile);
      }

      copy($tempFile, $destination);
      unlink($tempFile);
    }
  }
  closedir($vz);
}
