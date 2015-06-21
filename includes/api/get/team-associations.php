<?php
$output['team-associations'] = $db->getRows("
  SELECT *
  FROM (
    SELECT `team_id`,`person_id`
    FROM `scores`
  UNION ALL
    SELECT `team_id`,`person_id`
    FROM `group_scores` `gs`
    INNER JOIN `person_participations` `p` ON `p`.`score_id` = `gs`.`id`
  ) `union`
  WHERE `team_id` IS NOT NULL
  AND `person_id` IS NOT NULL
  GROUP BY `team_id`, `person_id`
");
$output['success'] = true;
