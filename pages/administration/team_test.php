<?php

$teams = $db->getRows("
    SELECT *
    FROM `teams`
    ORDER BY `name`
");

foreach ($teams as $team) {
    $links = $db->getRows("
              SELECT *
              FROM `links`
              WHERE `for_id` = '".$team['id']."'
              AND `for` = 'team'
            ");
    if (count($links)) continue;

    echo '<a href="http://www.google.com/search?q=Feuerwehrsport+'.str_replace(' ', '+', $team['name']).'&amp;hl=de">'.$team['name'].'</a> - ';
    echo '<a href="?page=team&amp;id='.$team['id'].'">#</a> ';
    echo $team['logo'];
    echo '<br>';
}
