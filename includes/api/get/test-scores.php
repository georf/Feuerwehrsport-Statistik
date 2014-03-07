<?php

Check2::except()->isAdmin();


$discipline = Check2::except()->post('discipline')->isDiscipline();
$sex        = Check2::except()->post('sex')->isSex();
$rawScores  = Check2::except()->post('rawScores')->present();
$seperator  = Check2::except()->post('seperator')->present();
$headlines  = explode(",", Check2::except()->post('headlines')->present());
$score_lines = explode("\n", $rawScores);

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
  $score['teams']       = array('');
  $score['team_ids']    = array('-1');
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
          $teamName = $cols[$i];
          $score['teams'] = array($teamName);
          $score['oldteam'] = $teamName;

          $score['team_number'] = Import::getTeamNumber($teamName, $score['team_number']);
          if (is_numeric($teamName) && Check::isIn($teamName, 'teams')) {
            $score['team_ids'] = array($teamName);
            $tryTeam = FSS::tableRow('teams', $teamId);
            $score['teams'] = array($tryTeam['short']);
            break;
          }

          $tryTeamIds = Import::getTeamIds($teamName);
          if (count($tryTeamIds)) {
            $score['team_ids'] = $tryTeamIds;
            $score['teams'] = array();
            foreach ($tryTeamIds as $teamId) {
              $tryTeam = FSS::tableRow('teams', $teamId);
              $score['teams'][] = $tryTeam['short'];
            }
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
      $score['persons'] = Import::getPersons($score['name'], $score['firstname'], $sex);
    }
  }
  $outputScores[] = $score;
}

$output['success'] = true;
$output['scores']  = $outputScores;
$output['teams']   = $outputTeams;