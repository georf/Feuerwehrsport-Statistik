<?php
$teamId = Check2::except()->post('teamId')->isIn('teams');
Check2::except()->isSubAdmin();

TempDB::generate('x_full_competitions');

foreach (array('fs', 'la', 'gs') as $discipline) {
  $output[$discipline] = $db->getRows("
    SELECT `gs`.`id`, `gs`.`team_number`, `gsc`.`competition_id`, `c`.`name`, `c`.`date`, `c`.`place`, `c`.`event`,
      COALESCE(`gs`.`time`, ".FSS::INVALID.") AS `time`,`gs`.`sex`,`gs`.`run`
    FROM `group_scores` `gs` 
    INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
    INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
    INNER JOIN `x_full_competitions` `c` ON `c`.`id` = `gsc`.`competition_id`
    WHERE `gs`.`team_id` = '".$teamId."'
    AND `gst`.`discipline` = '".$discipline."'
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
