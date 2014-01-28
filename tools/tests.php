#!/usr/bin/php
<?php (PHP_SAPI === 'cli') || exit();

// read config file
require_once(__DIR__.'/../includes/lib/init.php');

$path = __DIR__.'/Tests/';
include_once($path.'basic.php');

$vz = opendir($path);
while ($file = readdir($vz)) {
  if (preg_match('|\.php$|', $file)) {
    include_once($path.$file);
  }
}
closedir($vz);

$errors = array();
$classes = get_declared_classes();
foreach ($classes as $class) {
  if (preg_match('|Test$|', $class)) {
    $errors = array_merge($errors, call_user_func($class.'::run'));
  }
}

echo "\n";
foreach ($errors as $error) {
  echo $error."\n";
}