<?php

$teams = $db->getRows("
  SELECT `id`, `name`, `short`, `type`, `state`, `logo`
  FROM `teams`
");

foreach ($teams as $key => $team) {
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

  $scores = $db->getRows("
    SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`
    FROM (
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,NULL AS `person_7`
        FROM `scores_gs`
        WHERE `team_id` = '".$team['id']."'
      UNION
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,`person_5`,`person_6`,`person_7`
        FROM `scores_la`
        WHERE `team_id` = '".$team['id']."'
      UNION
        SELECT `person_1`,`person_2`,`person_3`,`person_4`,NULL AS `person_5`,NULL AS `person_6`, NULL AS `person_7`
        FROM `scores_fs`
        WHERE `team_id` = '".$team['id']."'
    ) `i`
  ");

  foreach ($scores as $score) {
    for($i = 1; $i <= 7; $i++) {
      if (empty($score['person_'.$i])) continue;

      $pid = $score['person_'.$i];
      if (!isset($members[$pid])) $members[$pid] = true;
    }
  }

  $teams[$key]['members'] = count($members);
  $teams[$key]['competitions'] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM (
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
    ) `c`
  ", 'count');
}


echo Title::set('Mannschaften');

$empty = array();
$small = array('class' => 'small');

echo Bootstrap::row()->col(CountTable::build($teams)
->col('Name', function ($row) { return Link::team($row['id'], $row['name']); }, 15)
->col('Abk.', 'short', 12)
->col('Typ', 'type', 5, $small)
->col('Land', 'state', 4, array('title' => function ($row) { return FSS::stateToText($row['state']); }), $small)
->col('Mitglieder', function ($row) { return FSS::countNoEmpty($row['members']); }, 4, $empty, $small) 
->col('Wettkämpfe', function ($row) { return FSS::countNoEmpty($row['competitions']); }, 4, $empty, $small)
->col('', function ($row) { return TeamLogo::get($row['id'], $row['logo']); }, 2, array('style' => 'padding:0'))
, 12);

echo Title::h2('Neue Mannschaft anlegen', 'mannschaftanlegen');
echo Bootstrap::row()
->col(
  '<p>Ist deine Mannschaft oder Feuerwehr noch nicht eingetragen? Dann lege sie doch einfach schnell an. Mit nur ein paar Klicks und ohne Anmeldung ist es in einer Minute geschafft.</p>'.
  '<p><button id="add-team">Mannschaft hinzufügen</button></p>', 4)
->col('<img src="/styling/images/user-group-new-tall.png" alt=""/>', 3)
->col(
  '<h4>Konventionen - Freiwillige Feuerwehren</h4>'.
  '<p>Bei der Eingabe einer Feuerwehr einigen wir uns der Übersichts geschuldet auf folgende Abkürzung:</p>'.
  '<table><tr><th>Name:</th><td>FF XXX</td></tr><th>Abk.:</th><td>XXX</td></tr></table>', 5);