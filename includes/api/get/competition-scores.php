<?php
$competition_id = Check2::except()->post('competition_id')->isIn('competitions');

$scores = array();
$scores['gs'] = array();
$scores['gs']['female'] = $db->getFirstRow("
  SELECT COUNT(*) AS `count`
  FROM `scores_gs`
  WHERE `competition_id` = ".$competition_id, 'count');

$scores['hl'] = array();
$scores['hl']['male'] = $db->getFirstRow("
  SELECT COUNT(`s`.`id`) AS `count`
  FROM `scores` `s`
  INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
  WHERE `p`.`sex` = 'male'
  AND `s`.`discipline` = 'hl'
  AND `competition_id` = ".$competition_id, 'count');

$scores['hb'] = array();
$scores['la'] = array();
$scores['fs'] = array();

foreach (array('male', 'female') as $sex) {
  $scores['hb'][$sex] = $db->getFirstRow("
    SELECT COUNT(`s`.`id`) AS `count`
    FROM `scores` `s`
    INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
    WHERE `p`.`sex` = '".$sex."'
    AND `s`.`discipline` = 'hb'
    AND `competition_id` = ".$competition_id, 'count');

  $scores['la'][$sex] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_la`
    WHERE `sex` = '".$sex."'
    AND `competition_id` = ".$competition_id, 'count');

  $scores['fs'][$sex] = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores_fs`
    WHERE `sex` = '".$sex."'
    AND `competition_id` = ".$competition_id, 'count');
}

$output['scores'] = $scores;
$output['success'] = true;
