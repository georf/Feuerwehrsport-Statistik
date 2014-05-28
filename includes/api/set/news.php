<?php

Check2::except()->isAdmin();
$date    = Check2::except()->post('date')->isDate();
$title   = Check2::except()->post('title')->present();
$content = Check2::except()->post('content')->present();
$newsId  = Check2::except()->post('id')->isIn('news');

$resultId = $db->updateRow('news', $newsId, array(
  'date'    => trim($date),
  'title'   => trim($title),
  'content' => trim($content),
));

Log::insert('set-news', FSS::tableRow('news', $resultId));
$output['success'] = true;
