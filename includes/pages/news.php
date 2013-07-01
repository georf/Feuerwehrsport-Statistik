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
        echo Link::news($prev['id'], gDate($prev['date']), $prev['title']);
    }



    $next = $db->getFirstRow("
        SELECT *
        FROM `news`
        WHERE `date` > '".$news['date']."'
        ORDER BY `date` ASC
        LIMIT 1;
    ");

    if ($next) {
        echo Link::news($next['id'], gDate($next['date']), $next['title']);
    }

    echo '<p><a href="/page/news.html">Übersicht</a></p>';

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
        echo '<td><h3>'.Link::news($new['id'], $new['title'], gDate($new['date'])).'</h3></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td></td>';
        echo '<td style="padding-bottom:30px;">'.htmlspecialchars(mb_substr(strip_tags($new['content']),0,300, 'UTF-8')).' '.Link::news($new['id'], '[...]', $new['title']).'</td>';
        echo '</tr>';
    }

    echo '</table>';

}
