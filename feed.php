<?php

try {
    require_once(__DIR__.'/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

$link_news = 'http://statistik.feuerwehrsport-teammv.de/?page=news&id=';

new FeedLoader();

if (isset($_GET['type']) && $_GET['type'] == 'news') {
      
    //Creating an instance of RSS2FeedWriter class. 
    $TestFeed = new RSS2FeedWriter();

    //Setting the channel elements
    //Use wrapper functions for common channel elements
    $TestFeed->setTitle('Feuerwehrsport - Statistiken - Neuigkeiten');
    $TestFeed->setLink('http://statistik.feuerwehrsport-teammv.de');
    $TestFeed->setDescription('Neuigkeiten über die Statistiken vom Feuerwehrsport');

    $news = $db->getRows("
        SELECT *
        FROM `news`
        ORDER BY `date` DESC
        LIMIT 10;
    ");

    foreach ($news as $new) {
        
        $item = $TestFeed->createNewItem();

        $item->setTitle(htmlspecialchars_decode($new['title']));
        $item->setLink($link_news.$new['id']);
        $item->setDate(strtotime($new['date']));
        $item->setDescription(htmlspecialchars_decode($new['content']));

        $TestFeed->addItem($item);
    }
    
    $TestFeed->generateFeed();
}
