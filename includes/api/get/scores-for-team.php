<?php
$teamId = Check2::except()->post('teamId')->isIn('teams');
Check2::except()->isAdmin();

TempDB::generate('x_full_competitions');

foreach (array('fs', 'la', 'gs') as $discipline) {
  $output[$discipline] = $db->getRows("
    SELECT `s`.`id`, `s`.`team_number`, `s`.`competition_id`, `c`.`name`, `c`.`date`, `c`.`place`, `c`.`event`,
      COALESCE(`s`.`time`, ".FSS::INVALID.") AS `time`,
      ".($discipline != 'gs'? "`s`.":"'female' AS ")."`sex`,
      ".($discipline == 'fs'? "`s`.":"'' AS ")."`run`
    FROM `scores_".$discipline."` `s`
    INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
    WHERE `s`.`team_id` = '".$teamId."'
    ORDER BY `c`.`date` DESC
  "); 
}

$output['single'] = $db->getRows("
  SELECT `s`.`id`, `s`.`team_number`, `s`.`competition_id`, `c`.`name`, `c`.`date`, `c`.`place`, `c`.`event`,
    COALESCE(`s`.`time`, ".FSS::INVALID.") AS `time`
  FROM `scores` `s`
  INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `s`.`competition_id`
  WHERE `s`.`team_id` = '".$teamId."'
  ORDER BY `c`.`date` DESC
"); 

$output['success'] = true;
