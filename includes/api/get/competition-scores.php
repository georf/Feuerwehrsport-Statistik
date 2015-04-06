<?php
$competitionId = Check2::except()->post('competitionId')->isIn('competitions');

$scores = array(
  'hl' => array(),
  'hb' => array(),
);

foreach (FSS::$disciplines as $discipline) {
  $scores[$discipline] = array();
  foreach (array('male', 'female') as $sex) {
    if (FSS::isSingleDiscipline($discipline)) {
      $scores[$discipline][$sex] = $db->getFirstRow("
        SELECT COUNT(`s`.`id`) AS `count`
        FROM `scores` `s`
        INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
        WHERE `p`.`sex` = '".$sex."'
        AND `s`.`discipline` = '".$discipline."'
        AND `competition_id` = ".$competitionId, 'count');
    } else {
      $scores[$discipline][$sex] = $db->getFirstRow("
        SELECT COUNT(*) AS `count`
        FROM `group_scores` `gs`
        INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
        INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
        WHERE `gs`.`sex` = '".$sex."'
        AND `gst`.`discipline` = '".$discipline."'
        AND `gsc`.`competition_id` = '".$competitionId."'", 'count');
    }
  }
}

$output['scores'] = $scores;
$output['success'] = true;
