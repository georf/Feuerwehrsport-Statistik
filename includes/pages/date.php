<?php

$date = Check2::page()->get('id')->isIn('dates', 'row');
$id = $date['id'];

echo Title::set('Termin - '.$date['name'].' - '.gDate($date['date']));

$links = $db->getRows("
  SELECT *
  FROM `links`
  WHERE `for` = 'date'
  AND `for_id` = '".$id."'
");

$disciplines = explode(',', $date['disciplines']);
sort($disciplines);
foreach ($disciplines as $k => $dis) {
  $disciplines[$k] = FSS::dis2img(strtolower($dis));
}


$chartTable = ChartTable::build()
->row('Datum', gDate($date['date']))
->row('Disziplinen', implode(' ', $disciplines));
if (!empty($date['place_id'])) $chartTable->row('Ort', Link::place($date['place_id']));
if (!empty($date['event_id'])) $chartTable->row('Typ', Link::event($date['event_id']));

$boxContent = "";
if (count($links)) {
    $boxContent .= '<ul class="disc">';
    foreach ($links as $link) {
        $boxContent .= '<li>'.Link::a($link['url'], $link['name']).'</li>';
    }
    $boxContent .= '</ul>';
}
$boxContent .= '<p style="text-align:center;"><button id="add-link" data-for-id="'.$date['id'].'" data-for-table="date">Link hinzufügen</button></p>';
$boxContent .= '<p style="text-align:center;"><button id="change-date" data-date-id="'.$date['id'].'">Termin bearbeiten</button></p>';

echo Bootstrap::row()
->col('<p>'.nl2br(htmlspecialchars($date['description'])).'</p>', 8)
->col($chartTable.$boxContent, 4);



