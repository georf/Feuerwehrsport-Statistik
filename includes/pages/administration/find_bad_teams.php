<?php

if (isset($_GET['delete']) && Check::isIn($_GET['delete'], 'persons')) {
    $db->deleteRow('persons', $_GET['delete']);

    header('Location: ?page=administration&admin=find_bad_teams');
    exit();
}


$teams1 = $db->getRows("
    SELECT LOWER(`short`) AS `short`,
        LOWER(`name`) AS `name`,
        `id`
    FROM `teams`
");

$teams2 = $teams1;


echo '<table class="table"><tr><th>Name</th><th>Kurz</th><th>id</th></tr>';


foreach ($teams1 as $p1) {
    foreach ($teams2 as $p2) {
        if ($p1['id'] == $p2['id']) continue;

        $l1 = levenshtein($p1['short'], $p2['short']);
        //$l2 = levenshtein($p1['name'], $p2['name']);
        //$l3 = $l1+$l2;

        if ($l1 < 2) {
            echo '<tr><td><a href="/page-team-'.$p1['id'].'.html">'.$p1['name'].'</a></td><td>'.$p1['short'].'</td><td><a href="/page-team-'.$p2['id'].'.html">'.$p2['name'].'</a></td><td>'.$p2['short'].'</td><td>'.$l1.'</td></tr>';
        }
    }
}

echo '</table>';
