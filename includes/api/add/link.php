<?php

$name = Check2::except()->post('name')->present();
$for  = Check2::except()->post('for')->isIn(array('competition', 'team', 'date'));
$id   = Check2::except()->post('id')->isIn($for.'s');
$url  = Check2::except()->post('url')->present();

if (!preg_match('|^https?://|', $url)) {
  $url = 'http://'.$url;
}

$resultId = $db->insertRow('links', array(
  'name'   => $name,
  'for'    => $for,
  'for_id' => $id,
  'url'    => $url,
));

Log::insert('add-link', FSS::tableRow('links', $resultId));
$output['success'] = true;
