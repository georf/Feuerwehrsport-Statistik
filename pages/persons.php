<?php
Title::set('Wettkämpfer');
echo '<h1>Wettkämpfer</h1>';

$sexs = array(
    'female' => 'Weiblich',
    'male' => 'Männlich',
);

foreach ($sexs as $sex => $title) {
    $persons = $db->getRows("
        SELECT *
        FROM `persons`
        WHERE `sex` = '".$sex."'
    ");

    foreach ($persons as $key => $person) {
        $persons[$key]['hb'] = $db->getFirstRow("
            SELECT COUNT(`id`) AS `count`
            FROM `scores`
            WHERE `person_id` = '".$person['id']."'
            AND `discipline` = 'HB'
        ", 'count');

        if ($sex === 'male') {
            $persons[$key]['hl'] = $db->getFirstRow("
                SELECT COUNT(`id`) AS `count`
                FROM `scores`
                WHERE `person_id` = '".$person['id']."'
                AND `discipline` = 'HL'
            ", 'count');
        } else {
            $persons[$key]['gs'] = $db->getFirstRow("
                SELECT COUNT(`id`) AS `count`
                FROM `scores_gruppenstafette`
                WHERE `person_1` = '".$person['id']."'
                OR `person_2` = '".$person['id']."'
                OR `person_3` = '".$person['id']."'
                OR `person_4` = '".$person['id']."'
                OR `person_5` = '".$person['id']."'
                OR `person_6` = '".$person['id']."'
            ", 'count');
        }

        $persons[$key]['la'] = $db->getFirstRow("
            SELECT COUNT(`id`) AS `count`
            FROM `scores_loeschangriff`
            WHERE `person_1` = '".$person['id']."'
            OR `person_2` = '".$person['id']."'
            OR `person_3` = '".$person['id']."'
            OR `person_4` = '".$person['id']."'
            OR `person_5` = '".$person['id']."'
            OR `person_6` = '".$person['id']."'
            OR `person_7` = '".$person['id']."'
        ", 'count');

        $persons[$key]['fs'] = $db->getFirstRow("
            SELECT COUNT(`id`) AS `count`
            FROM `scores_stafette`
            WHERE `person_1` = '".$person['id']."'
            OR `person_2` = '".$person['id']."'
            OR `person_3` = '".$person['id']."'
            OR `person_4` = '".$person['id']."'
        ", 'count');
    }

    echo '<h2>'.$title.'</h2>
        <table class="datatable">
            <thead>
              <tr>
                <th style="width:25%">Name</th>
                <th style="width:25%">Vorname</th>
                <th style="width:9%">HB</th>
                <th style="width:9%">'.(($sex === 'male')? 'HL':'GS').'</th>
                <th style="width:9%">LA</th>
                <th style="width:9%">FS</th>
                <th style="width:13%"></th>
              </tr>
            </thead>
            <tbody>';

    foreach ($persons as $person) {

        echo
            '<tr><td>',
                htmlspecialchars($person['name']),
            '</td><td>',
                htmlspecialchars($person['firstname']),
            '</td><td>',
                $person['hb'],
            '</td><td>',
                $person[($sex === 'male')? 'hl':'gs'],
            '</td><td>',
                $person['la'],
            '</td><td>',
                $person['fs'],
            '</td><td>',
                Link::person($person['id'], 'Details', $person['name'], $person['firstname']),
            '</td></tr>';
    }

    echo '</tbody></table>';
}
