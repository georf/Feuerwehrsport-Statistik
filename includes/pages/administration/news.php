<?php

Check2::page()->isAdmin();

$footerTags[] = '<script type="text/javascript" src="/js/jhtmlarea.js"></script>';
$footerTags[] = '<link href="/css/jhtmlarea.css" type="text/css" rel="stylesheet"/>';

echo Title::h1("News");

$news = $db->getRows("
  SELECT *
  FROM `news`
  ORDER BY `date` DESC
");

echo '<button class="add-news">Hinzufügen</button>';
echo '<table class="table">';
foreach ($news as $new) {
  echo '<tr>';
  echo '<td>'.gDate($new['date']).'</td>';
  echo '<td>'.htmlspecialchars($new['title']).'</td>';
  echo '<td><button class="edit-news" data-id="'.$new['id'].'">Bearbeiten</button></td>';
  echo '<td>'.htmlspecialchars(mb_substr(strip_tags($new['content']),0,300, 'UTF-8')).'</td>';
  echo '</tr>';
}
echo '</table>';
