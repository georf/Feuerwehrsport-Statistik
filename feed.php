<?php

try {
    require_once(__DIR__.'/includes/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}


$link_news = 'http://www.feuerwehrsport-statistik.de/?page=news&id=';
$link_logs = 'http://www.feuerwehrsport-statistik.de/?page=logs#logId';

new FeedLoader();



new RSS2FeedWriter();
new ATOMFeedWriter();

if (!isset($_GET['type']) || !in_array($_GET['type'], array('news', 'logs'))) exit();

$feed = Cache::get();
if (!$feed) {

    $feed = new RSS2FeedWriter();
    if (isset($_GET['v']) && $_GET['v'] == 'atom') $feed = new ATOMFeedWriter();

    $feed->setLink('http://www.feuerwehrsport-statistik.de');
    switch ($_GET['type']) {
        case 'news':

            $feed->setTitle('Feuerwehrsport - Statistiken - Neuigkeiten');
            $feed->setDescription('Neuigkeiten über die Statistiken vom Feuerwehrsport');

            $news = $db->getRows("
                SELECT *
                FROM `news`
                ORDER BY `date` DESC
                LIMIT 10;
            ");

            foreach ($news as $new) {

                $item = $feed->createNewItem();

                $item->setTitle($new['title']);
                $item->setLink($link_news.$new['id']);
                $item->setDate(strtotime($new['date']));
                $item->setDescription($new['content']);

                $feed->addItem($item);
            }
        break;


        case 'logs':

            $feed->setTitle('Feuerwehrsport - Statistiken - Veränderungen');
            $feed->setDescription('Veränderungen der Statistiken vom Feuerwehrsport');

            $logs = $db->getRows("
                SELECT *
                FROM `logs`
                ORDER BY `inserted` DESC
                LIMIT 100;
            ");

            foreach ($logs as $log) {
                $log = Log::getByRow($log);

                $item = $feed->createNewItem();

                $item->setTitle($log->description());
                $item->setLink($link_logs.$log->id);
                $item->setDate($log->time());
                $item->setDescription($log->content());

                $feed->addItem($item);
            }
        break;

    }

    Cache::put($feed);
}

$feed->generateFeed();
