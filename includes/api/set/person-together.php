<?php

Check2::except()->isSubAdmin();
$person        = Check2::except()->post('personId')->isIn('persons', 'row');
$correctPerson = Check2::except()->post('newPersonId')->isIn('persons', 'row');
    
if (Check2::value()->post("always")->present()) {
  $db->insertRow('persons_spelling', array(
    'name' => $person['name'],
    'firstname' => $person['firstname'],
    'sex' => $person['sex'],
    'person_id' => $correctPerson['id'],
  ));
}
    
// set scores
$scores = $db->getRows("
  SELECT `id`
  FROM `scores`
  WHERE `person_id` = '".$person['id']."'
");
foreach ($scores as $score) {
  $db->updateRow('scores', $score['id'], array('person_id' => $correctPerson['id']));
}

// set scores
$scores = $db->getRows("
  SELECT `id`
  FROM `person_participations`
  WHERE `person_id` = '".$person['id']."'
");
foreach ($scores as $score) {
  $db->updateRow('person_participations', $score['id'], array('person_id' => $correctPerson['id']));
}
      
// set spelling
$spellings = $db->getRows("
  SELECT `id`
  FROM `persons_spelling`
  WHERE `person_id` = '".$person['id']."'
");
foreach ($spellings as $spell) {
  $db->updateRow('persons_spelling', $spell['id'], array('person_id' => $correctPerson['id']));
}

// delete person
$db->deleteRow('persons', $person['id']);

Log::insertWithAlert('set-person-together', array(
  "old" => $person,
  "correct" => $correctPerson
));
$output['success'] = true;
