<?php

if (!Check::post('name', 'short', 'type')) throw new Exception('need more infos');
if (empty($_POST['name'])) throw new Exception('name is empty');
if (empty($_POST['short'])) throw new Exception('short is empty');
if (!in_array($_POST['type'], array('Team','Feuerwehr'))) throw new Exception('type is bad');

$newid = $db->insertRow('teams', array(
    'name' => $_POST['name'],
    'short' => $_POST['short'],
    'type' => $_POST['type'],
));


Log::insert('add-team', FSS::tableRow('teams', $newid));
$output['success'] = true;
