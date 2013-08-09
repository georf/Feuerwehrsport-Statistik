<?php

try {
    require_once(__DIR__.'/../includes/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

$rows = $db->getRows("
    SELECT *
    FROM `teams`
    WHERE `lat` IS NOT NULL
");

foreach ($rows as $row) {
    Map::downloadStaticMap('teams', $row['id']);

    sleep(1);
}
