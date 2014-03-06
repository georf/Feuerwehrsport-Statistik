<?php

$news = Check2::boolean()->get('id')->isIn('news', 'row');
if ($news) {
  echo Bootstrap::row()
  ->col(Title::set('Neuigkeiten - '.$news['title']), 9)
  ->col(gDate($news['date']), 3);

  echo Bootstrap::row()->col($news['content'], 12);

  $bottom = Bootstrap::row();

  $prev = $db->getFirstRow("
    SELECT *
    FROM `news`
    WHERE `date` < '".$news['date']."'
    ORDER BY `date` DESC
    LIMIT 1;
  ");

  if ($prev) {
    $bottom->col(Link::news($prev['id'], gDate($prev['date']).' - '.$prev['title'], $prev['title']), 5, array('text-center'));
  }

  $next = $db->getFirstRow("
    SELECT *
    FROM `news`
    WHERE `date` > '".$news['date']."'
    ORDER BY `date` ASC
    LIMIT 1;
  ");
  
  $bottom->col('<p><a href="/page/news.html">Übersicht</a></p>', 2, array('text-center'));

  if ($next) {
    $bottom->col(Link::news($next['id'], gDate($next['date']).' - '.$next['title'], $next['title']), 5, array('text-center'));
  }
  echo $bottom;

} else {
  echo Title::set('Neuigkeiten');
  echo Bootstrap::row()
  ->col('<p>Hier werden alle Neuigkeiten zu dieser Feuerwehrsport-Statistik aufgelistet.</p>', 12);
  $news = $db->getRows("
    SELECT *
    FROM `news`
    ORDER BY `date` DESC
  ");
  foreach ($news as $new) {
    echo Bootstrap::row()
    ->col('', 1)
    ->col(gDate($new['date']), 1)
    ->col('', 1)
    ->col(
      '<h3>'.Link::news($new['id'], $new['title'], gDate($new['date'])).'</h3>'.
      '<p>'.htmlspecialchars(mb_substr(strip_tags($new['content']),0,290, 'UTF-8')).' '.Link::news($new['id'], '[...]', $new['title']).'</p>'
    , 8)
    ->col('', 1);
  }
}
