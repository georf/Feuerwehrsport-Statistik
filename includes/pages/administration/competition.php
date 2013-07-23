<?php

if (!isset($_GET['id']) || !Check::isIn($_GET['id'], 'competitions')) throw new PageNotFound();

$_id = $_GET['id'];



$competition = $db->getFirstRow("
    SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`,
        `t`.`persons`,`t`.`run`,`t`.`score`,`t`.`id` AS `score_type`
    FROM `competitions` `c`
    INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
    INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
    LEFT JOIN `score_types` `t` ON `t`.`id` = `c`.`score_type_id`
    WHERE `c`.`id` = '".$db->escape($_id)."'
    LIMIT 1;
");

$id = $competition['id'];

echo '<h1>',
    htmlspecialchars($competition['event']),' - ',
    htmlspecialchars($competition['place']),' - ',
    gdate($competition['date']),
'</h1>';

echo '<a href="/page-competition-'.$id.'.html">Zurück</a>';

if (isset($_POST['la-type'])) {
    if (isset($config['la'][$_POST['la-type']])) {
        $db->updateRow('competitions', $id, array(
            'la' => $_POST['la-type']
        ));

        $competition['la'] = $_POST['la-type'];
    } else {
        $db->updateRow('competitions', $id, array(
            'la' => NULL
        ));

        $competition['la'] = NULL;
    }
}


if (isset($_POST['fs-type'])) {
    if (isset($config['fs'][$_POST['fs-type']])) {
        $db->updateRow('competitions', $id, array(
            'fs' => $_POST['fs-type']
        ));

        $competition['fs'] = $_POST['fs-type'];
    } else {
        $db->updateRow('competitions', $id, array(
            'fs' => NULL
        ));

        $competition['fs'] = NULL;
    }
}




$update = array();
foreach ($config['missed'] as $key=>$value) {
    if (isset($_POST['missed-'.$key]) && $_POST['missed-'.$key] == 'true') {
        $update[] = $key;
    }
}
if (count($update)) {
    $db->updateRow('competitions', $id, array(
        'missed' => implode(',', $update)
    ));

    $competition['missed'] = implode(',', $update);
}

echo '<div class="row">';

echo '<div class="six columns">';
echo '<form method="post">';
$m = explode(',',$competition['missed']);
foreach ($config['missed'] as $key=>$value) {
    echo '<input type="checkbox" ';
    if (in_array($key, $m)) {
        echo ' checked="checked" ';
    }
    echo ' name="missed-'.$key.'" value="true" id="missed-'.$key.'"/><label for="missed-'.$key.'">'.$value.'</label><br/>';
}
echo '<button type="submit">Speichern</button></form>';
echo '</div>';


echo '<div class="six columns">';
echo '<form method="post">';

echo '<select name="la-type">';
echo '<option value="" ';
if (!$competition['la']) {
    echo ' selected="selected" ';
}
echo ' />Nicht gelaufen</option>';

foreach ($config['la'] as $key=>$value) {
    echo '<option value="'.$key.'" ';
    if ($key == $competition['la']) {
        echo ' selected="selected" ';
    }
    echo ' />'.$value.'</option>';
}
echo '</select>';
echo '<button type="submit">Speichern</button></form>';
echo '</div>';


echo '<div class="six columns">';
echo '<form method="post">';

echo '<select name="fs-type">';
echo '<option value="" ';
if (!$competition['fs']) {
    echo ' selected="selected" ';
}
echo ' />Nicht gelaufen</option>';

foreach ($config['fs'] as $key=>$value) {
    echo '<option value="'.$key.'" ';
    if ($key == $competition['fs']) {
        echo ' selected="selected" ';
    }
    echo ' />'.$value.'</option>';
}
echo '</select>';
echo '<button type="submit">Speichern</button></form>';
echo '</div>';

echo '<span class="bt user-group-new" id="add-score-type" title="Mannschaftswertung ändern">&nbsp;</span>';

echo '</div>';

?>
<script>
$('#add-score-type').click(function() {
        checkLogin(function() {
            wPost('get-score-types', {}, function(data) {
                var options = [];
                options.push({
                    value: 0,
                    display: 'Keine'
                });
                var t, i, l = data.types.length;
                for (i = 0; i < l; i++) {
                    t = data.types[i];
                    options.push({
                        value: t.id,
                        display: t.persons + '/'+ t.run + '/' + t.score
                    });
                }

                var w = new FormWindow([
                    ['Select', 'score_type_id', 'Wertung', 0, 'Die Zahlen bedeuten:<ol><li>Mannschaftsstärke</li><li>Läufer pro Disziplin</li><li>Wertungen</li></ol>', {options: options}]
                ], 'Mannschaftswertung hinzufügen', 'Bitte wählen Sie den Wertungstyp aus:');

                w.submit(function(data) {
                    this.close();

                    data.id = <?=$_id?>;

                    wPost('set-score-type', data, function() {
                        location.reload();
                    });
                }).open();
            });
        });
    });
</script>
