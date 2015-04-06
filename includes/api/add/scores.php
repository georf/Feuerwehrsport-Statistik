<?php

Check2::except()->isAdmin();

$discipline           = Check2::except()->post('discipline')->isDiscipline();
$sex                  = Check2::except()->post('sex')->isSex();
$scores               = Check2::except()->post('scores')->isArray();

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
      'time' => $score['times'][$i],
      'team_number' => $teamNumber,
      'team_id' => $score['team_id']
    );

    if (FSS::isSingleDiscipline($discipline)) {
      $competitionId = Check2::except()->post('competitionId')->isIn('competitions');

      $insert['competition_id'] = $competitionId;
      $insert['person_id']      = $person['id'];
      $insert['discipline']     = $discipline;
      $db->insertRow('scores', $insert, false);

    } else {
      $groupScoreCategoryId = Check2::except()->post('groupScoreCategoryId')->isIn('group_score_categories');
      $insert['group_score_category_id'] = $groupScoreCategoryId;
      $insert['sex']                     = $sex;
      if ($discipline == 'fs') $insert['run'] = $score['run'];
      
      $db->insertRow('group_scores', $insert, false);

    }
  }
}

Cache::clean();
$output['success'] = true;