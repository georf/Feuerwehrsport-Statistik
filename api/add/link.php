<?php

if (!Check::post('name', 'url', 'id', 'for')) throw new Exception("bad request");
if (!in_array($_POST['for'], array('competition', 'team'))) throw new Exception("for is bad");

$table = $_POST['for'].'s';
if (!Check::isIn($_POST['id'], $table)) throw new Exception("id not found");

$_name = $_POST['name'];
$_url = $_POST['url'];

if (!preg_match('|^https?://|', $_url)) {
    $_url = 'http://'.$_url;
}

$result = $db->insertRow('links', array(
  'for_id' => $_POST['id'],
  'for'    => $_POST['for'],
  'name'   => $_POST['name'],
  'url'    => $_url,
));

$output['success'] = true;

// generate log
Log::insert('add-link', FSS::tableRow('links', FSS::tableRow('links', $result)));
