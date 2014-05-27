<?php
function __autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require __DIR__.'/vendor/'.$fileName;
}

function post($key)
{
  if (isset($_POST[$key])) {
    return $_POST[$key];
  }
  return '';
}

function gDate($date)
{
    return date('d.m.Y', strtotime($date));
}

function c2s($centi) {
    return intval($centi)/100;
}

function time2stringD($centi) {
    if (!$centi) return 'D';
    else return time2string($centi);
}

function time2string($centi) {
    return sprintf('%.2f', c2s($centi));
}