<?php

try {
    require_once(__DIR__.'/../includes/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

$rows = $db->getRows("
    SELECT *
    FROM `teams`
    WHERE `type` = 'Feuerwehr'
    ORDER BY `id`
");

foreach ($rows as $row) {
    echo $row['short']."\n";
    $objects = json_decode(file_get_contents('http://nominatim.openstreetmap.org/search?q='.urlencode($row['short'].', Deutschland').'&format=json'), true);

    if (count($objects) == 0) continue;

    print_r($objects[0]);

    mysql_query("
        UPDATE `teams`
        SET
        `lat` = '".$db->escape($objects[0]['lat'])."',
        `lon` = '".$db->escape($objects[0]['lon'])."'
        WHERE `id` = '".$row['id']."'
        LIMIT 1
    "
    );
    usleep(589);

    Map::downloadStaticMap('teams', $row['id']);

    usleep(589);
}
