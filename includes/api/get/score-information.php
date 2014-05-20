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
} elseif ($discipline === 'gs') {
  $score = Check2::except()->post('scoreId')->isIn('scores_gs', 'row');
  $scores = $db->getRows("
    SELECT
      `s`.`id`,`team_id`,`team_number`,`competition_id`,`time`,
      `p1`.`person_id` AS `person_1`,
      `p2`.`person_id` AS `person_2`,
      `p3`.`person_id` AS `person_3`,
      `p4`.`person_id` AS `person_4`,
      `p5`.`person_id` AS `person_5`,
      `p6`.`person_id` AS `person_6`
    FROM `scores_gs` `s`
    LEFT JOIN `person_participations_gs` `p1` ON `p1`.`score_id` =  `s`.`id` AND `p1`.`position` = 1
    LEFT JOIN `person_participations_gs` `p2` ON `p2`.`score_id` =  `s`.`id` AND `p2`.`position` = 2
    LEFT JOIN `person_participations_gs` `p3` ON `p3`.`score_id` =  `s`.`id` AND `p3`.`position` = 3
    LEFT JOIN `person_participations_gs` `p4` ON `p4`.`score_id` =  `s`.`id` AND `p4`.`position` = 4
    LEFT JOIN `person_participations_gs` `p5` ON `p5`.`score_id` =  `s`.`id` AND `p5`.`position` = 5
    LEFT JOIN `person_participations_gs` `p6` ON `p6`.`score_id` =  `s`.`id` AND `p6`.`position` = 6    
    WHERE `team_id` = '".$score['team_id']."'
    AND `team_number` = '".$score['team_number']."'
    AND `competition_id` = '".$score['competition_id']."'
  ");
} elseif ($discipline === 'fs') {
  $score = Check2::except()->post('scoreId')->isIn('scores_fs', 'row');
  $scores = $db->getRows("
    SELECT
      `s`.`id`,`team_id`,`team_number`,`sex`,`competition_id`,`time`,
      `p1`.`person_id` AS `person_1`,
      `p2`.`person_id` AS `person_2`,
      `p3`.`person_id` AS `person_3`,
      `p4`.`person_id` AS `person_4`
    FROM `scores_fs` `s`
    LEFT JOIN `person_participations_fs` `p1` ON `p1`.`score_id` =  `s`.`id` AND `p1`.`position` = 1
    LEFT JOIN `person_participations_fs` `p2` ON `p2`.`score_id` =  `s`.`id` AND `p2`.`position` = 2
    LEFT JOIN `person_participations_fs` `p3` ON `p3`.`score_id` =  `s`.`id` AND `p3`.`position` = 3
    LEFT JOIN `person_participations_fs` `p4` ON `p4`.`score_id` =  `s`.`id` AND `p4`.`position` = 4
    WHERE `team_id` = '".$score['team_id']."'
    AND `team_number` = '".$score['team_number']."'
    AND `sex` = '".$score['sex']."'
    AND `competition_id` = '".$score['competition_id']."'
  ");
} elseif ($discipline === 'la') {
  $score = Check2::except()->post('scoreId')->isIn('scores_la', 'row');
  $scores = $db->getRows("
    SELECT
      `s`.`id`,`team_id`,`team_number`,`sex`,`competition_id`,`time`,
      `p1`.`person_id` AS `person_1`,
      `p2`.`person_id` AS `person_2`,
      `p3`.`person_id` AS `person_3`,
      `p4`.`person_id` AS `person_4`,
      `p5`.`person_id` AS `person_5`,
      `p6`.`person_id` AS `person_6`,
      `p7`.`person_id` AS `person_7`
    FROM `scores_la` `s`
    LEFT JOIN `person_participations_la` `p1` ON `p1`.`score_id` =  `s`.`id` AND `p1`.`position` = 1
    LEFT JOIN `person_participations_la` `p2` ON `p2`.`score_id` =  `s`.`id` AND `p2`.`position` = 2
    LEFT JOIN `person_participations_la` `p3` ON `p3`.`score_id` =  `s`.`id` AND `p3`.`position` = 3
    LEFT JOIN `person_participations_la` `p4` ON `p4`.`score_id` =  `s`.`id` AND `p4`.`position` = 4
    LEFT JOIN `person_participations_la` `p5` ON `p5`.`score_id` =  `s`.`id` AND `p5`.`position` = 5
    LEFT JOIN `person_participations_la` `p6` ON `p6`.`score_id` =  `s`.`id` AND `p6`.`position` = 6
    LEFT JOIN `person_participations_la` `p7` ON `p7`.`score_id` =  `s`.`id` AND `p7`.`position` = 7
    WHERE `team_id` = '".$score['team_id']."'
    AND `team_number` = '".$score['team_number']."'
    AND `sex` = '".$score['sex']."'
    AND `competition_id` = '".$score['competition_id']."'
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
