<?php

Check2::except()->isAdmin();
$date    = Check2::except()->post('date')->isDate();
$title   = Check2::except()->post('title')->present();
$content = Check2::except()->post('content')->present();

$resultId = $db->insertRow('news', array(
  'date'    => trim($date),
  'title'   => trim($title),
  'content' => trim($content),
));

Log::insert('add-news', FSS::tableRow('news', $resultId));
$output['success'] = true;
