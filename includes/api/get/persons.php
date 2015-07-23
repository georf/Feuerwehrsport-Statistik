<?php

$where = '';
$sex = Check2::value(false)->post('sex')->isSex();
if ($sex) {
  $where = " WHERE `sex`  = '".$sex."' ";
}

$output['persons'] = $db->getRows("
  SELECT `name`,`firstname`,`id`,`sex`,
    EXISTS (
      SELECT 1 
      FROM scores_dcup_single_u u 
      INNER JOIN scores s ON s.id = u.score_id
      INNER JOIN dcups d ON d.id = u.dcup_id AND d.year = YEAR(NOW())
      WHERE s.person_id = persons.id 
    ) AS youth
  FROM `persons`
  ".$where."
  ORDER BY `name`, `firstname`
");
$output['success'] = true;
