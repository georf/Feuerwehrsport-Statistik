<?php

Check2::page()->isAdmin();

$competitionId = Check2::value()->post('competitionId')->isIn('competitions');
$dcupId = Check2::value()->post('dcupId')->isIn('dcups');

if ($competitionId && $dcupId) {
  $scores = DcupCalculation::single($competitionId, 'HB', 'female');
  $scores = array_merge($scores, DcupCalculation::single($competitionId, 'HL', 'female'));
  $scores = array_merge($scores, DcupCalculation::single($competitionId, 'HB', 'male'));
  $scores = array_merge($scores, DcupCalculation::single($competitionId, 'HL', 'male'));
  DcupCalculation::insertSingle($scores, $dcupId);
  DcupCalculation::zk($competitionId, $dcupId);
  DcupCalculation::calculate();
  header('Location: ?page=administration&admin=dcup_single');
  exit();
}

$removeCompetitionId = Check2::value()->post('removeCompetitionId')->isIn('competitions');
if ($removeCompetitionId) {
  $count = 0;
  foreach ($db->getRows("
    SELECT `d`.`id`
    FROM `scores_dcup_single` `d`
    INNER JOIN `scores` `s` ON `d`.`score_id` = `s`.`id`
    WHERE `s`.`competition_id` = '".$removeCompetitionId."'
  ", 'id') as $id) {
    $db->deleteRow("scores_dcup_single", $id, 'id', false);
    $count++;
  }
  foreach ($db->getRows("
    SELECT `id`
    FROM `scores_dcup_zk`
    WHERE `competition_id` = '".$removeCompetitionId."'
  ", 'id') as $id) {
    $db->deleteRow("scores_dcup_zk", $id, 'id', false);
    $count++;
  }
  echo $count;
  DcupCalculation::calculate();
}


$removeYouthCompetitionId = Check2::value()->post('removeYouthCompetitionId')->isIn('competitions');
if ($removeYouthCompetitionId) {
  $count = 0;
  foreach ($db->getRows("
    SELECT `d`.`id`
    FROM `scores_dcup_single_u` `d`
    INNER JOIN `scores` `s` ON `d`.`score_id` = `s`.`id`
    WHERE `s`.`competition_id` = '".$removeYouthCompetitionId."'
  ", 'id') as $id) {
    $db->deleteRow("scores_dcup_single_u", $id, 'id', false);
    $count++;
  }
  foreach ($db->getRows("
    SELECT `id`
    FROM `scores_dcup_zk_u`
    WHERE `competition_id` = '".$removeYouthCompetitionId."'
  ", 'id') as $id) {
    $db->deleteRow("scores_dcup_zk_u", $id, 'id', false);
    $count++;
  }
  echo $count;
  DcupCalculation::calculate();
}

if (Check2::value()->post('u20')->getVal()) {
  echo 'JOOOOOOOOOOOOOOOOOOOO';
  # only for U20
  DcupCalculation::calculate();
  DcupCalculation::calculate(true);
}

TempDB::generate('x_full_competitions');

echo '<form method="post" action="">';
echo '<input type="hidden" name="u20" value="true"/>';
echo '<button>U20 berechnen</button>';
echo '</form>';

echo '<form method="post" action="">';
echo '<select name="competitionId">';
foreach ($db->getRows("
  SELECT * 
  FROM `x_full_competitions`
  WHERE `event_id` = 1
  ORDER BY `date` 
  DESC") as $competition) {
  echo '<option value="'.$competition['id'].'">'.$competition['date'].' - '.$competition['place'].' - '.$competition['event'].'</option>';
}
echo '</select>';
echo '<select name="dcupId">';
foreach ($db->getRows("
  SELECT * 
  FROM `dcups`
  ORDER BY `year` 
  DESC") as $dcup) {
  echo '<option value="'.$dcup['id'].'">'.$dcup['year'].'</option>';
}
echo '</select>';
echo '<button>Berechnen</button>';
echo '</form>';


echo '<table class="table">';
foreach ($db->getRows("SELECT * FROM `dcups` ORDER BY `year` DESC") as $dcup) {
  echo '<tr><th colspan="3">'.$dcup['year'].'</th><th>'.$dcup['ready'].'</th></tr>';
  foreach ($db->getRows("
    SELECT c.*, COUNT(d.id) as `count`
    FROM `scores_dcup_single` `d`
    INNER JOIN `scores` `s` ON `d`.`score_id` = `s`.`id`
    INNER JOIN `x_full_competitions` `c` ON `s`.`competition_id` = `c`.`id`
    WHERE `dcup_id` = '".$dcup['id']."'
    GROUP BY `s`.`competition_id`
    ORDER BY `date` DESC
  ") as $competition) {
    $youth = $db->getFirstRow("
      SELECT Count(u.id) as c
      FROM scores_dcup_single_u u
      INNER JOIN `scores` `su` ON `u`.`score_id` = `su`.`id` AND su.competition_id = ".$competition['id']."
    ", 'c');
    $zk = $db->getFirstRow("
      SELECT Count(u.id) as c
      FROM scores_dcup_zk u
      WHERE u.competition_id = ".$competition['id']."
    ", 'c');
    $zkYouth = $db->getFirstRow("
      SELECT Count(u.id) as c
      FROM scores_dcup_zk_u u
      WHERE u.competition_id = ".$competition['id']."
    ", 'c');
    echo '<tr>';
    echo '<td>'.$competition['date'].'</td>';
    echo '<td>'.$competition['place'].'</td>';
    echo '<td>'.$competition['event'].' ('.$competition['count'].'/'.$youth.'/'.$zk.'/'.$zkYouth.')';
    echo  '<button data-competition-id="'.$competition['id'].'" data-dcup-id="'.$dcup['id'].'" class="add-youth">Youth</button></td>';
    echo '<td><form method="post" action="">';
    echo '<input type="hidden" name="removeYouthCompetitionId" value="'.$competition['id'].'"/>';
    echo '<button onclick="return confirm(\'Wirklich?\');">U Entfernen</button>';
    echo '</form><form method="post" action="">';
    echo '<input type="hidden" name="removeCompetitionId" value="'.$competition['id'].'"/>';
    echo '<button onclick="return confirm(\'Wirklich?\');">Entfernen</button>';
    echo '</form></td>';
    echo '</tr>';
  }
}
echo '</table>';