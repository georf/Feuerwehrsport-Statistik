<?php
$year = Check2::except()->post('year')->match('|^\d{4}$|');

$dcupId = $db->getFirstRow("SELECT `id` FROM `dcups` WHERE `year` = '".$year."' LIMIT 1", 'id');

$results = array();
foreach (FSS::$singleDisciplinesWithDoubleEvent as $discipline) {
  foreach (FSS::$sexes as $sex) {
    foreach (array(true, false) as $youth) {
      $key = $discipline.'-'.$sex.($youth ? '-youth' : '');
      $u = $youth ? '_u' : '';

      if ($discipline == 'zk') {
        $results[$key] = $db->getRows("
          SELECT `points`, `time`, `competition_id`, `person_id`
          FROM scores_dcup_zk".$u." s
            INNER JOIN persons p on p.id = s.person_id
          WHERE dcup_id = '".$dcupId."'
            AND sex = '".$sex."'
        ");
      } else {
        $results[$key] = $db->getRows("
          SELECT `points`, `time`, `competition_id`, `person_id`
          FROM scores_dcup_single".$u." ds
            INNER JOIN scores s on s.id = ds.score_id
            INNER JOIN persons p on p.id = s.person_id
          WHERE dcup_id = '".$dcupId."'
            AND discipline = '".$discipline."'
            AND sex = '".$sex."'
        ");
      }
    }
  }
}
$output['results'] = $results;
$output['success'] = true;
