<?php

if (!isset($_GET['id']) || !Check::isIn($_GET['id'], 'dates')) throw new PageNotFound();

$date = FSS::tableRow('dates', $_GET['id']);

Title::set('Termin - '.$date['name'].' - '.gDate($date['date']));

$links = $db->getRows("
    SELECT *
    FROM `links`
    WHERE `for` = 'date'
    AND `for_id` = '".$date['id']."'
");


$disciplines = explode(',', $date['disciplines']);
sort($disciplines);
foreach ($disciplines as $k => $dis) {
    $disciplines[$k] = FSS::dis2img(strtolower($dis));
}

echo '<h1>'.gDate($date['date']).' - '.htmlspecialchars($date['name']).'</h1>';

echo '<div style="border:1px solid #D9ECFF;float: right;width:260px;">';
echo '<table>';
echo '<tr><th>Datum:</th><td>'.gDate($date['date']).'</td></tr>';
echo '<tr><th>Disziplinen:</th><td>'.implode(' ', $disciplines).'</td></tr>';
if (!empty($date['place_id'])) echo '<tr><th>Ort:</th><td>'.Link::place($date['place_id']).'</td></tr>';
if (!empty($date['event_id'])) echo '<tr><th>Typ:</th><td>'.Link::event($date['event_id']).'</td></tr>';
echo '</table>';

if (count($links)) {
    echo '<ul class="disc">';
    foreach ($links as $link) {
        echo '<li>',Link::a($link['url'], $link['name']),'</li>';
    }
    echo '</ul>';
}
echo '<p style="text-align:center;"><button id="add-link" data-for-id="'.$date['id'].'" data-for-table="date">Link hinzufügen</button></p>';
echo '</div>';


echo '<p>'.nl2br(htmlspecialchars($date['description'])).'</p>';


