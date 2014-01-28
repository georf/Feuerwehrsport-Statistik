<?php

Check2::except()->isAdmin();

$competition_id = Check2::except()->post('competition_id')->isIn('competitions');
$discipline     = Check2::except()->post('discipline')->isDiscipline();
$sex            = Check2::except()->post('sex')->isSex();
$scores         = Check2::except()->post('scores')->isArray();

foreach ($scores as $score) {
  $team_number = strval(intval($score['team_number']) -1);

  if (FSS::isSingleDiscipline($discipline)) {
    $person = Import::getPerson($score['name'], $score['firstname'], $sex);
    if (!$person) {
      $result = $db->insertRow('persons', array(
        'name' => $score['name'],
        'firstname' => $score['firstname'],
        'sex' => $sex
      ), false);
      $person = FSS::tableRow('persons', $result);
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
      'competition_id' => $competition_id,
      'time' => $score['times'][$i],
      'team_number' => $team_number,
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