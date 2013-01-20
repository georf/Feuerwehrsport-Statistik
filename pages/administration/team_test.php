<?php

$teams = $db->getRows("
    SELECT *
    FROM `teams`
    WHERE `website` IS NULL
");

foreach ($teams as $team) {
    echo '<a href="http://www.google.com/search?q=Feuerwehrsport+'.str_replace(' ', '+', $team['name']).'&amp;hl=de">'.$team['name'].'</a> - ';
    echo '<a href="?page=team&amp;id='.$team['id'].'">#</a>';
    echo '<br>';
}
