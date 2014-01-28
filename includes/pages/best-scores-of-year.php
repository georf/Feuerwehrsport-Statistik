<?php
Title::set('Überblick');

if (!Check::get('id') || !preg_match('|^[1,2][0-9]{3}$|', $_GET['id'])) throw new PageNotFound();
$_year = $_GET['id'];

$disciplines = array(
    array('hb', 'female'),
    array('hb', 'male'),
    array('hl', 'male'),
);

echo '<h1>Übersicht der Bestzeiten für das Jahr '.$_year.'</h1>';
echo '<div class="five columns">';
echo '<div class="toc"><h5>Inhaltsverzeichnis</h5><ol>';
foreach ($disciplines as $d) {
    echo '<li><a href="#'.$d[0].'-'.$d[1].'">',FSS::dis2name($d[0]),' ',FSS::sex($d[1]),'</a></li>';
}
echo '</ol></div></div>';
echo '<div class="five columns">';
echo '<p>Die Tabellen zeigen die gesammelten Bestzeiten für das Jahr '.$_year.' in den Einzeldisziplinen. Einen Überblick über das Jahr gibt es '.Link::year($_year, 'hier').'.</p>';
echo '</div>';
echo '<div class="five columns">';
echo '<ul class="disc">';
echo '<li>'.Link::year($_year, 'Jahresübersicht').'</li>';
echo '<li>'.Link::bestPerformanceOfYear($_year, 'Bestleistungen des Jahres').'</li>';
echo '</ul>';
echo '</div>';

foreach ($disciplines as $d) {
    $dis = $d[0];
    $sex = $d[1];

    echo '<h2 id="'.$d[0].'-'.$d[1].'">',FSS::dis2name($dis),' ',FSS::sex($sex),'</h2>',
    '<div class="five columns">',FSS::dis2img($dis, 'blue'),
    '</div>',
    '<div class="nine columns">',
    '<table class="table" style="width:100%">';
    $scores = $db->getRows("
        SELECT * 
        FROM (

            SELECT  `s` . * ,  `e`.`name` AS  `event` , 
              `p`.`name`, `p`.`firstname`, `p`.`sex`, `c`.`date`
            FROM  `scores`  `s` 
            INNER JOIN  `competitions`  `c` ON  `c`.`id` =  `s`.`competition_id` 
            INNER JOIN  `events`  `e` ON  `e`.`id` =  `c`.`event_id` 
            INNER JOIN  `persons`  `p` ON  `p`.`id` =  `s`.`person_id` 
            WHERE YEAR(`c`.`date`) = '".$db->escape($_year)."'
            AND `discipline` = '".$dis."'
            AND `p`.`sex` = '".$sex."'
            AND  `time` IS NOT NULL 
            ORDER BY  `s`.`time`
        ) `inner` 
        GROUP BY  `person_id` 
        ORDER BY  `time`
    ");

    $i = 1;
    foreach ($scores as $score) {
        echo '<tr><td>',$i,'</td><td>',
            Link::person($score['person_id'], $score['firstname'].' '.$score['name']),'</td><td>',
            FSS::time($score['time']),'</td><td>'.Link::competition($score['competition_id'], $score['event'].' - '.gDate($score['date'])).'</td></tr>';
        $i++;
    }

    echo '</table></div>';
}
