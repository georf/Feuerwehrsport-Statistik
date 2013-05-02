<?php

$persons1 = $db->getRows("
select p1.*
from persons p1
inner join (
SELECT `name`,COUNT(name) as c  FROM `persons`
GROUP BY name
order by c desc
) p2 on p1.name = p2.name
where p2.c > 1
order by p2.name
");



echo '<table class="table"><tr><th>Name</th><th>Vorname</th><th>sex</th><th>id</th></tr>';


foreach ($persons1 as $p1) {
    echo '<tr><td><a href="?page=person&amp;id='.$p1['id'].'">'.$p1['name'].'</a></td><td>'.$p1['firstname'].'</td><td>'.$p1['sex'].'</td><td></td></tr>';
}

echo '</table>';
