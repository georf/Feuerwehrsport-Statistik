<?php

session_start();

// include needed files
require_once(__DIR__.'/functions.php');
require_once(__DIR__.'/config.php');


// try to connect to database
global $db;

$db = new Database(
    $config['database']['server'],
    $config['database']['database'],
    $config['database']['username'],
    $config['database']['password']
);
