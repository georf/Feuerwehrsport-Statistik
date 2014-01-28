<?php

Check2::except()->isAdmin();

$discipline = Check2::except()->post('discipline')->isDiscipline();
$sex        = Check2::except()->post('sex')->isSex();
$raw_scores = Check2::except()->post('raw_scores')->present();
$seperator  = Check2::except()->post('seperator')->present();
$headlines  = explode(",", Check2::except()->post('headlines')->present());
$score_lines = explode("\n", $raw_scores);

$outputScores = array();
$outputTeams = array();

$required = array('time');
if (FSS::isSingleDiscipline($discipline)) {
  $required[] = 'firstname';
  $required[] = 'name';
} else {
  $required[] = 'team';
}

$correct = true;
foreach ($required as $value) if (!in_array($value, $headlines)) {
  $correct = false;
}

foreach ($score_lines as $score_line) {
  $score = array();
  $score['line']    = $score_line;
  $score['correct'] = $correct;

  $score_line = trim($score_line);
  $cols = str_getcsv($score_line, $seperator);

  $score['times']       = array();
  if (FSS::isSingleDiscipline($discipline)) {
    $score['name']      = '';
    $score['firstname'] = '';
  }
  $score['team']        = '';
  $score['team_id']     = '-1';
  $score['team_number'] = '1';
  $score['oldteam']     = '';
  $score['number']      = '1';

  if (count($cols) < count($headlines)) {
    $score['correct'] = false;
  } else {

    for ($i = 0; $i < count($headlines); $i++) {
      $cols[$i] = trim($cols[$i]);
      switch (trim($headlines[$i])) {
        case 'name':
          $score['name'] = preg_replace('|,$|','',preg_replace('|^,|','',$cols[$i]));
          break;

        case 'firstname':
          $score['firstname'] = preg_replace('|,$|','',preg_replace('|^,|','',$cols[$i]));
          break;

        case 'time':
          $time = Import::getTime($cols[$i]);
          if ($time === null) {
            $time = 'NULL';
          } elseif ($time === false) {
            $time = -1;
          }
          $score['times'][] = $time;
          break;

        case 'team':
          $score['team'] = $cols[$i];
          $score['oldteam'] = $score['team'];

          $score['team_number'] = Import::getTeamNumber($score['team'], $score['team_number']);
          if (is_numeric($score['team']) && Check::isIn($score['team'], 'teams')) {
            $score['team_id'] = $score['team'];
            $try_team = FSS::tableRow('teams', $score['team']);
            $score['team'] = $try_team['short'];
            break;
          }

          $try_team_id = Import::getTeamId($score['team']);
          if ($try_team_id !== false) {
            $score['team_id'] = $try_team_id;
            $try_team = FSS::tableRow('teams', $try_team_id);
            $score['team'] = $try_team['short'];
            break;
          }
          $outputTeams[] = $score['oldteam'];
          $score['correct'] = false;
          break;

        case 'run':
          $score['run'] = strtoupper($cols[$i]);
          if (!in_array($score['run'], array('A', 'B'))) {
            $score['run'] = 'A';
          }
          break;
      }
    }

    if (FSS::isSingleDiscipline($discipline)) {
      $score['found_person'] = true;
      $person = Import::getPerson($score['name'], $score['firstname'], $sex);
      if ($person) {
        $score['name']      = $person['name'];
        $score['firstname'] = $person['firstname'];
      } else {
        $score['found_person'] = false;
      }
    }
  }
  $outputScores[] = $score;
}

$output['success'] = true;
$output['scores']  = $outputScores;
$output['teams']   = $outputTeams;