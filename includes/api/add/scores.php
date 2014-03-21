<?php

Check2::except()->isAdmin();

$competitionId = Check2::except()->post('competitionId')->isIn('competitions');
$discipline    = Check2::except()->post('discipline')->isDiscipline();
$sex           = Check2::except()->post('sex')->isSex();
$scores        = Check2::except()->post('scores')->isArray();

foreach ($scores as $score) {
  $teamNumber = strval(intval($score['team_number']) -1);

  if (FSS::isSingleDiscipline($discipline)) {
    if (isset($score['person_id'])) {
      $person = FSS::tableRow('persons', $score['person_id']);
    } else {
      $persons = Import::getPersons($score['name'], $score['firstname'], $sex);
      if (count($persons)) {
        $person = $persons[0];
      } else {
        $result = $db->insertRow('persons', array(
          'name' => trim($score['name']),
          'firstname' => trim($score['firstname']),
          'sex' => trim($sex)
        ), false);
        $person = FSS::tableRow('persons', $result);
      }
    }

    if ($score['team_id'] == -1) {
      $score['team_id'] = NULL;
    }
  }

  for ($i=0; $i < count($score['times']); $i++) { 
    if ($score['times'][$i] == 'NULL') {
      $score['times'][$i] = NULL;
    }

    $insert = array(
      'competition_id' => $competitionId,
      'time' => $score['times'][$i],
      'team_number' => $teamNumber,
      'team_id' => $score['team_id']
    );

    if (FSS::isSingleDiscipline($discipline)) {

      $insert['person_id']  = $person['id'];
      $insert['discipline'] = $discipline;
      $db->insertRow('scores', $insert, false);

    } elseif ($discipline == 'fs') {

      $insert['sex']  = $sex;
      $insert['run']  = $score['run'];
      $db->insertRow('scores_fs', $insert, false);

    } elseif ($discipline == 'la') {

      $insert['sex']  = $sex;
      $db->insertRow('scores_la', $insert, false);

    } elseif ($discipline == 'gs') {

      $db->insertRow('scores_gs', $insert, false);

    }
  }
}

Cache::clean();
$output['success'] = true;