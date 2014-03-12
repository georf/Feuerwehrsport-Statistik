<?php
$competitionId = Check2::except()->post('competitionId')->isIn('competitions');
$teamId        = Check2::except()->post('teamId')->isIn('teams');
$discipline    = Check2::except()->post('discipline')->isDiscipline();
$sex           = Check2::except()->post('sex')->isSex();
$teamNumber    = Check2::except()->post('teamNumber')->isNumber();

$competition = FSS::competition($competitionId);

$output['score'] = false;
if (FSS::isGroupDiscipline($discipline)) {
  $scores = $db->getRows("
    SELECT MIN(COALESCE(`time`, ".FSS::INVALID.")) AS `time`, `team_id`, `team_number`, 
      CONCAT(`team_id`,'-',`team_number`) AS `unique`
    FROM `scores_".$discipline."`
    WHERE `competition_id` = '".$competitionId."'
    ".($discipline != 'gs'? " AND `sex` = '".$sex."' ":'')."
    GROUP BY `unique`
    ORDER BY `time`
  ");
  $points = 10;
  for ($i = 0; $i < count($scores); $i++) {
    $scores[$i]['points'] = $points;
    $points--;
    if ($points < 0) $points = 0;

    if ($scores[$i]['team_id'] == $teamId && $scores[$i]['team_number'] == $teamNumber) {
      $output['score'] = $scores[$i];
      break;
    }
  }
} else {
  $scores = $db->getRows("
    SELECT `best`.*
    FROM (
      SELECT `id`,`team_id`,`team_number`,
      `person_id`,
      MIN(COALESCE(`time`, ".FSS::INVALID.")) AS `time`
      FROM `scores`
      WHERE `time` IS NOT NULL
      AND `competition_id` = '".$competitionId."'
      AND `discipline` = '".$discipline."'
      AND `team_number` != -2
      AND `team_id` IS NOT NULL
      GROUP BY `person_id`
    ) `best`
    INNER JOIN `persons` `p` ON `best`.`person_id` = `p`.`id`
    WHERE `sex` = '".$sex."'
  ");

  // Bereche die Wertung
  $teams = array();
  foreach ($scores as $score) {
    if ($score['team_number'] < 0) continue;
    if (!$score['team_id']) continue;

    $uniqTeam = $score['team_id'].'-'.$score['team_number'];
    if (!isset($teams[$uniqTeam])) {
      $teams[$uniqTeam] = array(
        'id' => $score['team_id'],
        'number' => $score['team_number'],
        'scores' => array(),
      );
    }

    $teams[$uniqTeam]['scores'][] = $score;
  }

  // sort every persons in teams
  foreach ($teams as $uniqTeam => $team) {
    $time = 0;

    usort($team['scores'], function($a, $b) {
      if ($a['time'] == $b['time']) return 0;
      elseif ($a['time'] > $b['time']) return 1;
      else return -1;
    });

    if (count($team['scores']) < $competition['score']) {
      $teams[$uniqTeam]['time'] = FSS::INVALID;
      continue;
    }

    for($i = 0; $i < $competition['score']; $i++) {
      if ($team['scores'][$i]['time'] == FSS::INVALID) {
        $teams[$uniqTeam]['time'] = FSS::INVALID;
        continue 2;
      }
      $time += $team['scores'][$i]['time'];
    }
    $teams[$uniqTeam]['time'] = $time;
  }

  // Sortiere Teams nach Zeit
  usort($teams, function ($a, $b) {
    if ($a['time'] == $b['time']) return 0;
    elseif ($a['time'] > $b['time']) return 1;
    else return -1;
  });

  $points = 10;
  for ($z = 0; $z < count($teams); $z++) {
    $teams[$z]['points'] = $points;
    $points--;
    if ($points < 0) $points = 0;

    if ($teams[$z]['id'] == $teamId && $teams[$z]['number'] == $teamNumber) {
      $output['score'] = $teams[$z];
      break;
    }
  }

}
$output['success'] = true;
