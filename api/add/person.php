<?php

if (!Check::post('name', 'firstname', 'sex')) throw new Exception('need more infos');
if (empty($_POST['name'])) throw new Exception('name is empty');
if (empty($_POST['firstname'])) throw new Exception('firstname is empty');
if (empty($_POST['sex'])) throw new Exception('sex is empty');
if (!in_array($_POST['sex'], array('female','male'))) throw new Exception('sex is bad');

$newid = $db->insertRow('persons', array(
    'name' => $_POST['name'],
    'firstname' => $_POST['firstname'],
    'sex' => $_POST['sex'],
));


Log::insert('add-person', FSS::tableRow('persons', $newid));
$output['success'] = true;
