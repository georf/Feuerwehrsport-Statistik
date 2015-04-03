<?php

echo new Foo;
$scores = $db->getRows("
  select p.*, s.time from persons p 
inner join scores s on s.person_id = p.id and s.discipline = 'HL'
inner join competitions c on c.id = s.competition_id and year(c.date) = 2013
where p.id 
IN (
  SELECT `person_id`
  FROM (
    SELECT 'GS' AS `discipline`,`p`.`person_id`,COUNT(`s`.`id`) AS `count`
    FROM `scores_gs` `s`
    INNER JOIN `person_participations_gs` `p` ON `p`.`score_id` = `s`.`id`
    WHERE `s`.`team_id` = '2'
    GROUP BY `p`.`person_id`
  UNION ALL
    SELECT 'FS' AS `discipline`,`p`.`person_id`,COUNT(`s`.`id`) AS `count`
    FROM `scores_fs` `s`
    INNER JOIN `person_participations_fs` `p` ON `p`.`score_id` = `s`.`id`
    WHERE `s`.`team_id` = '2'
    GROUP BY `p`.`person_id`
  UNION ALL
    SELECT 'LA' AS `discipline`,`p`.`person_id`,COUNT(`s`.`id`) AS `count`
    FROM `scores_la` `s`
    INNER JOIN `person_participations_la` `p` ON `p`.`score_id` = `s`.`id`
    WHERE `s`.`team_id` = '2'
    GROUP BY `p`.`person_id`
  UNION ALL
    SELECT `discipline`,`person_id`,COUNT(`id`) AS `count`
    FROM `scores`
    WHERE `team_id` = '2'
    AND `discipline` = 'HB'
    GROUP BY `person_id`
  UNION ALL
    SELECT `discipline`,`person_id`,COUNT(`id`) AS `count`
    FROM `scores`
    WHERE `team_id` = '2'
    AND `discipline` = 'HL'
    GROUP BY `person_id`
  ) `i`
)
");

$members = array();

foreach ($scores as $score) {
  $pid = $score['id'];
  if (!isset($members[$pid])) {
    $members[$pid] = $score;
    $members[$pid]["times"] = array();
    $members[$pid]["ds"] = array();
  }
  if (is_numeric($score["time"]))
    $members[$pid]["times"][] = $score["time"];
  else
    $members[$pid]["ds"][] = $score["time"];
}

echo "<table>";
foreach ($members as $key => $member) {
  echo "<tr>";
  sort($members[$key]["times"]);
  echo "<td>".$member['name']."</td>";
  echo "<td>".$member['firstname']."</td>";
  echo "<td>".$member['sex']."</td>";
  echo "<td>".count($member['times'])."</td>";
  echo "<td>".count($member['ds'])."</td>";
  echo "<td>".implode("</td><td>", $members[$key]["times"])."</td>";
  echo "</tr>";
}
echo "</table>";
print_r($members);
