<?php

Check2::except()->isSubAdmin();
$team        = Check2::except()->post('teamId')->isIn('teams', 'row');
$correctTeam = Check2::except()->post('newTeamId')->isIn('teams', 'row');
    
if (Check2::value()->post("always")->present()) {
  $db->insertRow('teams_spelling', array(
    'name' => $team['name'],
    'short' => $team['short'],
    'team_id' => $correctTeam['id'],
  ));
}
      
// set scores
$scores = $db->getRows("
  SELECT `id`
  FROM `scores`
  WHERE `team_id` = '".$team['id']."'
");
foreach ($scores as $score) {
  $db->updateRow('scores', $score['id'], array('team_id' => $correctTeam['id']));
}
    
// set scores
$scores = $db->getRows("
  SELECT `id`
  FROM `group_scores`
  WHERE `team_id` = '".$team['id']."'
");
foreach ($scores as $score) {
  $db->updateRow('group_scores', $score['id'], array('team_id' => $correctTeam['id']));
}

// set links
$links = $db->getRows("
  SELECT `id`
  FROM `links`
  WHERE `for` = 'team'
  AND `for_id` = '".$team['id']."'
");
foreach ($links as $link) {
  $db->updateRow('links', $link['id'], array('for_id' => $correctTeam['id']));
}
    
// set spelling
$spellings = $db->getRows("
  SELECT `id`
  FROM `teams_spelling`
  WHERE `team_id` = '".$team['id']."'
");
foreach ($spellings as $spell) {
  $db->updateRow('teams_spelling', $spell['id'], array('team_id' => $correctTeam['id']));
}
    
// delete team
$db->deleteRow('teams', $team['id']);


Log::insertWithAlert('set-team-together', array(
  "old" => $team,
  "correct" => $correctTeam
));
$output['success'] = true;
