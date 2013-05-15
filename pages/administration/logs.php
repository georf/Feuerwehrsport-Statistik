<h1>Logs</h1>


<table class="table" style="width:99%;">
<?php

$logs = $db->getRows("
    SELECT *
    FROM `logs`
    ORDER BY `inserted` DESC
    LIMIT 500;
");

foreach($logs as $log) {
    echo '<tr style="border-top:22px solid #E5E5E5;">';
    echo '<th>'.date('d.m.Y H:i', strtotime($log['inserted'])).'</th>';
    echo '<th>'.Login::getNameLink($log['user_id']).'</th>';
    echo '<th>'.$log['type'].'</th>';
    echo '</tr>';
    echo '<tr>';

    switch ($log['type']) {


        case 'table-row':
            $content = json_decode($log['content'], true);
            echo '<td colspan="2">Eintrag #'.$content['id'].' in Tabelle »'.$content['table'].'«</td>';
            echo '<td>'.json_encode(FSS::tableRow($content['table'], $content['id'])).'</td>';
        break;


        case 'score':
            $content = json_decode($log['content'], true);

            $person_id = 0;

            foreach ($content['scores'] as $score) {
                $s = $db->getFirstRow("
                    SELECT `s`.`time`,`p`.`name` AS `place`,`e`.`name` AS `event`,
                        `c`.`date`,`discipline`,`s`.`competition_id`,`c`.`place_id`,
                        `c`.`event_id`,`s`.`id` AS `score_id`,`s`.`person_id`
                    FROM `scores` `s`
                    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
                    INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
                    INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
                    WHERE `s`.`id` = '".$score['id']."'
                    LIMIT 1;
                ");
                echo '<td>'.$s['event'].'</td><td>'.$s['place'].'</td><td>'.$s['date'].' '.c2s($s['time']).'</td>';
                echo '</tr><tr>';
                $person_id = $s['person_id'];
            }

            $team = $db->getFirstRow("
                    SELECT `name`
                    FROM `teams`
                    WHERE `id` = '".$content['team']."'
                    LIMIT 1;
                ", 'name');

            $person = $db->getFirstRow("
                    SELECT CONCAT(`firstname`,' ',`name`) AS `n`
                    FROM `persons`
                    WHERE `id` = '".$person_id."'
                    LIMIT 1;
                ", 'n');

            echo '<td colspan="2">'.$person.'</td><td>'.$team.'</td>';

        break;


        default:
            echo '<td colspan="3">'.$log['content'].'</td>';
        break;
    }
    echo '</tr>';
}



?>
</table>
