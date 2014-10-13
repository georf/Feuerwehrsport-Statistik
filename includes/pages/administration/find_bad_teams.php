<?php

$teams1 = $teams2 = $db->getRows("
  SELECT LOWER(`short`) AS `short`,
    LOWER(`name`) AS `name`,
    `id`
  FROM `teams`
");

echo '<table class="table"><tr><th>Name</th><th>Kurz</th><th>Name</th><th>Kurz</th></tr>';
foreach ($teams1 as $p1) {
  foreach ($teams2 as $p2) {
    if ($p1['id'] == $p2['id']) continue;

    $l1 = levenshtein($p1['short'], $p2['short'], 1, 2, 3);

    if ($l1 < 2) {
      echo '<tr><td><a href="/page-team-'.$p1['id'].'.html">'.$p1['name'].'</a></td><td>'.$p1['short'].'</td><td><a href="/page-team-'.$p2['id'].'.html">'.$p2['name'].'</a></td><td>'.$p2['short'].'</td><td>'.$l1.'</td></tr>';
    }
  }
}
echo '</table>';
