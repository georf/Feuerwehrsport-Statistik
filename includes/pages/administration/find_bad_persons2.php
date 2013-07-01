<?php

if (isset($_GET['delete']) && Check::isIn($_GET['delete'], 'persons')) {
    $db->deleteRow('persons', $_GET['delete']);

    header('Location: ?page=administration&admin=find_bad_persons');
    exit();
}


$persons1 = $db->getRows("
    SELECT LOWER(`firstname`) AS `firstname`,
        LOWER(`name`) AS `name`,
        `id`,`sex`
    FROM `persons`
");

$persons2 = $persons1;


echo '<table class="table"><tr><th>Name</th><th>Vorname</th><th>sex</th><th>id</th></tr>';


foreach ($persons1 as $p1) {
    foreach ($persons2 as $p2) {
        if ($p1['id'] == $p2['id']) continue;
        if ($p1['sex'] != $p2['sex']) continue;

        $l1 = levenshtein($p1['firstname'], $p2['firstname']);
        $l2 = levenshtein($p1['name'], $p2['name']);
        $l3 = $l1+$l2;

        if ($l3 < 2) {
            echo '<tr><td><a href="/page-person-'.$p1['id'].'.html">'.$p1['name'].'</a></td><td>'.$p1['firstname'].'</td><td><a href="/page-person-'.$p2['id'].'.html">'.$p2['name'].'</a></td><td>'.$p2['firstname'].'</td><td>'.$l3.'</td></tr>';
        }
    }
}

echo '</table>';
