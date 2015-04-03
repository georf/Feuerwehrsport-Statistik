<?php
$competitionId = Check2::except()->post('competitionId')->isIn('competitions');

$scores = array();
$scores['gs'] = array();
$scores['gs']['female'] = $db->getFirstRow("
  SELECT COUNT(*) AS `count`
  FROM `scores_gs`
  WHERE `competition_id` = ".$competitionId, 'count');

$scores['hl'] = array();
$scores['hb'] = array();
$scores['la'] = array();
$scores['fs'] = array();

foreach (array('male', 'female') as $sex) {
  $scores['hl'][$sex] = $db->getFirstRow("
    SELECT COUNT(`s`.`id`) AS `count`
    FROM `scores` `s`
    INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
    WHERE `p`.`sex` = '".$sex."'
    AND `s`.`discipline` = 'hl'
    AND `competition_id` = ".$competitionId, 'count');

  $scores['hb'][$sex] = $db->getFirstRow("
    SELECT COUNT(`s`.`id`) AS `count`
    FROM `scores` `s`
    INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
    WHERE `p`.`sex` = '".$sex."'
    AND `s`.`discipline` = 'hb'
    AND `competition_id` = ".$competitionId, 'count');

  $scores['la'][$sex] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_la`
    WHERE `sex` = '".$sex."'
    AND `competition_id` = ".$competitionId, 'count');

  $scores['fs'][$sex] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_fs`
    WHERE `sex` = '".$sex."'
    AND `competition_id` = ".$competitionId, 'count');
}

$output['scores'] = $scores;
$output['success'] = true;
