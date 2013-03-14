<?php

if (isset($_GET['id']) && Check::isIn($_GET['id'], 'news')) {
    $news = $db->getFirstRow("
        SELECT *
        FROM `news`
        WHERE `id` = '".$db->escape($_GET['id'])."'
    ");

    echo '<p style="float:right;">'.gDate($news['date']).'</p>';
    echo '<h1>Neuigkeiten - '.htmlspecialchars($news['title']).'</h1>';

    Title::set('Neuigkeiten - '.htmlspecialchars($news['title']));

    echo $news['content'];

    echo '<div class="bottom-navi">';

    $prev = $db->getFirstRow("
        SELECT *
        FROM `news`
        WHERE `date` < '".$news['date']."'
        ORDER BY `date` DESC
        LIMIT 1;
    ");

    if ($prev) {
        echo '<a class="prev" href="?page=news&amp;id='.$prev['id'].'" title="'.gDate($prev['date']).'">« '.htmlspecialchars($prev['title']).'</a>';
    }



    $next = $db->getFirstRow("
        SELECT *
        FROM `news`
        WHERE `date` > '".$news['date']."'
        ORDER BY `date` ASC
        LIMIT 1;
    ");

    if ($next) {
        echo '<a class="next" href="?page=news&amp;id='.$next['id'].'" title="'.gDate($next['date']).'">'.htmlspecialchars($next['title']).' »</a>';
    }

    echo '<p><a href="?page=news">Übersicht</a></p>';

    echo '</div>';

} else {
    Title::set('Neuigkeiten');

    echo '<h1>Neuigkeiten</h1>';
    echo '<p>Hier werden alle Neuigkeiten zu dieser Feuerwehrsport-Statistik aufgelistet.</p>';

    echo '<table>';

    $news = $db->getRows("
        SELECT *
        FROM `news`
        ORDER BY `date` DESC
    ");


    foreach ($news as $new) {
        echo '<tr>';
        echo '<td>'.gDate($new['date']).'</td>';
        echo '<td><a href="?page=news&amp;id='.$new['id'].'"><h3>'.htmlspecialchars($new['title']).'</h3></a></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td></td>';
        echo '<td style="padding-bottom:30px;">'.htmlspecialchars(mb_substr(strip_tags($new['content']),0,300, 'UTF-8')).' <a href="?page=news&amp;id='.$new['id'].'">[...]</a></td>';
        echo '</tr>';
    }

    echo '</table>';

}
