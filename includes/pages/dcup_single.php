<?php

$disciplines = array(
    'hbf' => FSS::dis2name('hb').' - '.FSS::sex('female'),
    'hbm' => FSS::dis2name('hb').' - '.FSS::sex('male'),
    'hl' => FSS::dis2name('hl'),
    'zk' => FSS::dis2name('zk')
);

$path = $config['base'].'info/results/dcup/';
if (!Check::get('id') || !preg_match('|^[0-9]{4}$|', $_GET['id']) || !is_file($path.$_GET['id'].'.json')) throw new PageNotFound();
if (!Check::get('id2') || !isset($disciplines[$_GET['id2']])) throw new PageNotFound();

$year = $_GET['id'];
$path .= $year.'.json';
$id = $_GET['id2'];

echo '
<div class="row">
    <div class="five columns">
        '.FSS::dis2img(substr($id, 0, 2), true).'
    </div>
    <div class="ten columns">
        '.Title::set($disciplines[$id].' - Deutschlandpokal '.$year).'
        <p>Diese Seite zeigt die Gesamtwertung der D-Cup-Einzelergebnisse in der Kategorie »<em>'.$disciplines[$id].'</em>«. Dabei handelt es sich um selbst berechnete Daten, welche <b>nicht offiziell</b> sind.
        <br/>Zu der Gesamtwertung zitiere ich die Ausschreibung des DFV:</p>
        <p style="font-style:italic;padding-left:30px;">Bei Punktgleichheit von Wettkämpfern entscheidet die bessere Gesamtzeit der Bestzeiten aus den einzelnen Wettkämpfen über die bessere Platzierung. Hat ein Wettkämpfer eine geringere Anzahl von Wettkampfteilnahmen, ist er bei gleicher Gesamtpunktzahl automatisch hinter dem mit mehr Wettkämpfen platziert.</p>
    </div>
</div>';


$json = json_decode(file_get_contents($path), true);

echo '
    <table class="datatable">
    <thead>
      <tr>
        <th></th>
        <th>Name</th>
        <th>Vorname</th>';

foreach ($json['competitions'] as $competition_id) {
    $competition = FSS::competition($competition_id);
    echo '<th>'.$competition['place'].'</th>';
}

echo '
        <th>Bestzeit</th>
        <th>Teil.</th>
        <th>Gesamtzeit</th>
        <th>Punkte</th>
        <th></th>
      </tr>
    </thead>
    <tbody>';

$i = 0;
foreach ($json[$id] as $result) {
    $i++;

    // search person
    $person = $db->getFirstRow("
        SELECT *
        FROM `persons`
        WHERE `name` = '".$db->escape($result['name'])."'
        AND `firstname` = '".$db->escape($result['firstname'])."'
        LIMIT 1
    ");

    echo '<tr>';
    echo '<td>'.$i.'.</td>';
    echo '<td>'.$result['name'].'</td>';
    echo '<td>'.$result['firstname'].'</td>';
    foreach ($result['entries'] as $entry) {
        echo '<td>'.$entry['time'];
        if ($entry['points'] > 0) {
            echo ' ('.$entry['points'].')';
        }
        echo '</td>';
    }
    echo '<td>'.$result['bestTime'].'</td>';
    echo '<td>'.$result['part'].'</td>';
    echo '<td>'.$result['time'].'</td>';
    echo '<td>'.$result['points'].'</td>';
    echo '<td>'.Link::person($person['id']).'</td>';
    echo '</tr>';
}
echo '</tbody></table>';
