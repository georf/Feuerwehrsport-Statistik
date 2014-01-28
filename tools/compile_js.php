#!/usr/bin/php
<?php (PHP_SAPI === 'cli') || exit();

$path = __DIR__."/coffeescripts/";
$command = 'coffeescript-concat -I %s/classes/ %s | coffee -s -p > %s';
$command2 = 'ccjs %s';
$destinationPath = __DIR__."/../newjs/";

$vz = opendir($path);
while ($file = readdir($vz)) {
  if (is_file($path.$file) && preg_match('|\.coffee$|', $file)) {
    do $tempFile = '/tmp/'.rand(); while(is_file($tempFile));
    shell_exec(sprintf($command, $path, $path.$file, $tempFile));
    echo file_get_contents($tempFile);
    //echo shell_exec(sprintf($command2, $tempFile));

    copy($tempFile, $destinationPath.preg_replace('|\.coffee$|', '.js', $file));
    unlink($tempFile);
  }
}
closedir($vz);

