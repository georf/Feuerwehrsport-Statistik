<h1>Mannschaften</h1>
<div class="container">
  <table class="datatable">
    <thead>
      <tr>
        <th style="width:30%">Name</th>
        <th style="width:25%">Abk.</th>
        <th style="width:12%">Typ</th>
        <th style="width:8%">Mitglieder</th>
        <th style="width:8%">Wettkämpfe</th>
        <th style="width:8%"></th>
      </tr>
    </thead>
    <tbody>
<?php

Title::set('Mannschaften');


$teams = $db->getRows("
    SELECT *
    FROM `teams`
    ORDER BY `name`
");

foreach ($teams as $team) {
    $members = array();

    $scores = $db->getRows("
        SELECT `person_id`
        FROM `scores`
        WHERE `team_id` = '".$team['id']."'
        GROUP BY `person_id`
    ");
    foreach ($scores as $score) {
        $pid = $score['person_id'];
        if (!isset($members[$pid])) $members[$pid] = true;
    }

    // Gruppenstafette
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`
        FROM `scores_gs`
        WHERE `team_id` = '".$team['id']."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 6; $i++) {
            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = true;
        }
    }

    // Löschangriff
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`
        FROM `scores_la`
        WHERE `team_id` = '".$team['id']."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 7; $i++) {
            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = true;
        }
    }

    // Feuerwehrstafette
    $scores = $db->getRows("
        SELECT `person_1`,`person_2`,`person_3`,`person_4`
        FROM `scores_fs`
        WHERE `team_id` = '".$team['id']."'
    ");
    foreach ($scores as $score) {
        for($i = 1; $i <= 7; $i++) {
            if (empty($score['person_'.$i])) continue;

            $pid = $score['person_'.$i];
            if (!isset($members[$pid])) $members[$pid] = true;
        }
    }



    $competitions = $db->getRows("
        SELECT `competition_id`
        FROM (
            SELECT `competition_id`
            FROM `scores`
            WHERE `team_id` = '".$team['id']."'
            GROUP BY `competition_id`
        UNION
            SELECT `competition_id`
            FROM `scores_gs`
            WHERE `team_id` = '".$team['id']."'
            GROUP BY `competition_id`
        UNION
            SELECT `competition_id`
            FROM `scores_la`
            WHERE `team_id` = '".$team['id']."'
            GROUP BY `competition_id`
        UNION
            SELECT `competition_id`
            FROM `scores_fs`
            WHERE `team_id` = '".$team['id']."'
            GROUP BY `competition_id`
        ) `i`
        GROUP BY `competition_id`
    ");


    echo
    '<tr><td>',Link::team($team['id'], $team['name']),
    '</td><td>',htmlspecialchars($team['short']),'</td><td>',$team['type'],
    '</td><td>',count($members),'</td><td>',count($competitions),
    '</td><td style="padding:0">';

    if ($team['logo']) {
        if (!is_file($config['logo-path-mini'].$team['id'].'.png')) {
            $imageOutput = new Imagick($config['logo-path'].$team['logo']); // This will hold the resized image
            $imageOutput->cropThumbnailImage(24,24);
            $imageOutput->setImageFormat('png');
            $imageOutput->writeImage($config['logo-path-mini'].$team['id'].'.png'); // Write it to disk
            $imageOutput->clear();
            $imageOutput->destroy();
        }
        echo '<img src="/'.$config['logo-path-mini'].$team['id'].'.png" alt=""/>';
    }

    echo '</td></tr>';
}


?></tbody>
</table>
<h2>Neue Mannschaft anlegen</h2>
<p class="six columns">Ist deine Mannschaft oder Feuerwehr noch nicht eingetragen? Dann lege sie doch einfach schnell an. Mit nur ein paar Klicks und ohne Anmeldung ist es in einer Minute geschafft.</p>
<p class="two columns"></p>
<div class="six columns"><h3>Konventionen - Freiwillige Feuerwehren</h3><p>Bei der Eingabe einer Feuerwehr einigen wir uns der Übersichts geschuldet auf folgende Abkürzung:</p><table><tr><th>Name:</th><td>FF XXX</td></tr><th>Abk.:</th><td>XXX</td></tr></table></div>
<p><button id="add-team">Mannschaft hinzufügen</button></p>
</div>
