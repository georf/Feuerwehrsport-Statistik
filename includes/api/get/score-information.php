<?php
$scoreId   = Check2::except()->post('scoreId')->present();
$discipline = Check2::except()->post('discipline')->isIn(array('zk', 'fs', 'gs', 'la'));

$score = false;
$scores = false;

if ($discipline === 'zk') {
  $score = Check2::except()->post('scoreId')->isIn('scores', 'row');
  $scores = $db->getRows("
    SELECT *
    FROM `scores`
    WHERE `person_id` = '".$score['person_id']."'
    AND `competition_id` = '".$score['competition_id']."'
  ");
} elseif (FSS::isGroupDiscipline($discipline)) {
  $scoreId = Check2::except()->post('scoreId')->isIn('group_scores');
  $score = $db->getFirstRow("
    SELECT `gsc`.`competition_id`, `gst`.`discipline`, `gs`.*
    FROM `group_scores` `gs`
    INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
    INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
    WHERE `gs`.`id` = '".$scoreId."'
    LIMIT 1
  ");

  $scores = $db->getRows("
    SELECT
      `gs`.`id`,`team_id`,`team_number`,`competition_id`,`time`,
      `p1`.`person_id` AS `person_1`,
      `p2`.`person_id` AS `person_2`,
      `p3`.`person_id` AS `person_3`,
      `p4`.`person_id` AS `person_4`,
      `p5`.`person_id` AS `person_5`,
      `p6`.`person_id` AS `person_6`,
      `p7`.`person_id` AS `person_7`,
      `gs`.`sex`
    FROM `group_scores` `gs`
    INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
    INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
    LEFT JOIN `person_participations` `p1` ON `p1`.`score_id` =  `gs`.`id` AND `p1`.`position` = 1
    LEFT JOIN `person_participations` `p2` ON `p2`.`score_id` =  `gs`.`id` AND `p2`.`position` = 2
    LEFT JOIN `person_participations` `p3` ON `p3`.`score_id` =  `gs`.`id` AND `p3`.`position` = 3
    LEFT JOIN `person_participations` `p4` ON `p4`.`score_id` =  `gs`.`id` AND `p4`.`position` = 4
    LEFT JOIN `person_participations` `p5` ON `p5`.`score_id` =  `gs`.`id` AND `p5`.`position` = 5
    LEFT JOIN `person_participations` `p6` ON `p6`.`score_id` =  `gs`.`id` AND `p6`.`position` = 6    
    LEFT JOIN `person_participations` `p7` ON `p7`.`score_id` =  `gs`.`id` AND `p7`.`position` = 7    
    WHERE `gs`.`team_id` = '".$score['team_id']."'
    AND `gs`.`team_number` = '".$score['team_number']."'
    AND `gs`.`sex` = '".$score['sex']."'
    AND `gsc`.`competition_id` = '".$score['competition_id']."'
    AND `gst`.`discipline` = '".$score['discipline']."'
  ");
}

if ($scores === false) throw new Exception();

$score['timeHuman'] = FSS::time($score['time']);
$output['score'] = $score;

foreach ($scores as $discipline => $score) {
    $scores[$discipline]['timeHuman'] = FSS::time($score['time']);
}
$output['scores'] = $scores;
$output['success'] = true;
