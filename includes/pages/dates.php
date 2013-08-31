<?php

$dates = $db->getRows("
    SELECT
        `d`.`id`,`d`.`date`,`d`.`name`,`d`.`description`,`d`.`place_id`,
        `d`.`event_id`,
        `p`.`name` AS `place`, `d`.`disciplines`,`e`.`name` AS `event`
    FROM `dates` `d`
    LEFT JOIN `places` `p` ON `p`.`id` = `d`.`place_id`
    LEFT JOIN `events` `e` ON `e`.`id` = `d`.`event_id`
    ORDER BY `date` DESC
    LIMIT 10;
");
echo '<span style="float:right"><button id="add-date">Termin hinzufügen</button></span>';
echo Title::set('Wettkampf-Termine');



echo '<table class="datatable">';
echo '<thead><tr><th>Datum</th><th>Veranstaltung</th><th>Ort</th><th>Typ</th><th>Disziplinen</th><th></th></tr></thead>';
echo '<tbody>';
foreach ($dates as $date) {
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

    echo '<tr><td>'.$date['date'].'</td>';
    echo '<td>'.htmlspecialchars($date['name']).'</td>';
    echo '<td>';
    if (!empty($date['place_id'])) echo Link::place($date['place_id'], $date['place']);
    echo '</td><td>';
    if (!empty($date['event_id'])) echo Link::event($date['event_id'], $date['event']);
    echo '</td><td>'.implode(' ', $disciplines).'</td><td>'.Link::date($date['id']).'</td></tr>';

}
echo '</tbody>';
echo '</table>';
