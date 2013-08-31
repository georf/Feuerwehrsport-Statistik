<?php
if (!Check::post('date', 'name', 'place_id', 'event_id', 'description')) throw new Exception('bad input');

$disciplines = array('hl', 'hb', 'la', 'gs', 'fs');
$provided = array();
foreach ($disciplines as $dis) {
    if (Check::post($dis) && $_POST[$dis] == 'true') {
        $provided[] = strtoupper($dis);
    }
}
sort($provided);

$place_id = $_POST['place_id'];
if (!Check::isIn($place_id, 'places')) $place_id = NULL;

$event_id = $_POST['event_id'];
if (!Check::isIn($event_id, 'events')) $event_id = NULL;

$db->insertRow('dates', array(
    'date' => $_POST['date'],
    'name' => $_POST['name'],
    'place_id' => $place_id,
    'event_id' => $event_id,
    'description' => $_POST['description'],
    'disciplines' => implode(',', $provided)
));

$output['success'] = true;
