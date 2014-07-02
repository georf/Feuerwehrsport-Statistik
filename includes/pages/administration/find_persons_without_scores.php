<?php

$deleteId = Check2::value()->post('delete')->isIn('persons');
if ($deleteId) {
  $db->deleteRow('persons', $deleteId);
  header('Location: ?page=administration&admin=find_persons_without_scores');
  exit();
}

$persons = $db->getRows("
  SELECT *
  FROM `persons` p
  WHERE NOT EXISTS (
    SELECT 1
    FROM `scores` 
    WHERE person_id = p.id
    LIMIT 1
  ) AND NOT EXISTS (
    SELECT 1
    FROM `person_participations_fs` 
    WHERE person_id = p.id
    LIMIT 1
  ) AND NOT EXISTS (
    SELECT 1
    FROM `person_participations_gs` 
    WHERE person_id = p.id
    LIMIT 1
  ) AND NOT EXISTS (
    SELECT 1
    FROM `person_participations_la` 
    WHERE person_id = p.id
    LIMIT 1
  )
");

echo '<table class="table"><tr><th>Name</th><th>Vorname</th><th>sex</th><th>id</th></tr>';

foreach ($persons as $person) {
  echo '<tr><td>'.$person['name'].'</td><td>'.$person['firstname'].'</td><td>'.$person['sex'].'</td><td>';
  echo '<form method="post" action="">';
  echo '<input type="hidden" name="delete" value="'.$person['id'].'"/>';
  echo '<button onclick="return confirm(\'Wirklich löschen?\');">'.$person['id'].'</button>';
  echo '</form>';
  echo '</td></tr>';
}

echo '</table>';
