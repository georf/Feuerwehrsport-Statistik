<?php

$disciplines = array(
  'hbf' => array('hb', 'female', true),
  'hbm' => array('hb', 'male', true),
  'hl' => array('hl', 'male', false),
  'zk' => array('zk', 'male', false)
);

$path = $config['base'].'info/results/dcup/';
$year = Check2::page()->get('id')->match('|^[1,2][0-9]{3}$|');
Check2::page()->isTrue(is_file($path.$year.'.json'));
$key = Check2::page()->get('id2')->present();
Check2::page()->isTrue(isset($disciplines[$key]));
$path .= $year.'.json';

$discipline = $disciplines[$key][0];
$sex = $disciplines[$key][1];
$headline = FSS::dis2name($discipline);
if ($disciplines[$key][2]) $headline .= ' - '.FSS::sex($sex);

echo Bootstrap::row()
->col(FSS::dis2img($discipline, true), 4)
->col(
  Title::set($headline.' - Deutschlandpokal '.$year).
  '<p>Diese Seite zeigt die Gesamtwertung der D-Cup-Einzelergebnisse in der Kategorie »<em>'.$headline.'</em>«. Dabei handelt es sich um selbst berechnete Daten, welche <b>nicht offiziell</b> sind.'.
  '<br/>Zu der Gesamtwertung zitiere ich die Ausschreibung des DFV:</p>'.
  '<p style="font-style:italic;padding-left:30px;">Bei Punktgleichheit von Wettkämpfern entscheidet die bessere Gesamtzeit der Bestzeiten aus den einzelnen Wettkämpfen über die bessere Platzierung. Hat ein Wettkämpfer eine geringere Anzahl von Wettkampfteilnahmen, ist er bei gleicher Gesamtpunktzahl automatisch hinter dem mit mehr Wettkämpfen platziert.</p>'
, 8);

$json = json_decode(file_get_contents($path), true);

$i = 0;
$countTable = CountTable::build($json[$key])
->col('', function ($row) use (&$i) { $i++; return $i.'.'; }, 5)
->col('Name', 'name', 30)
->col('Vorame', 'firstname', 30);

foreach ($json['competitions'] as $count => $competition_id) {
  $competition = FSS::competition($competition_id);
  $countTable->col($competition['place'], function ($row) use ($count) { 
    $time = $row['entries'][$count]['time'];
    if ($row['entries'][$count]['points'] > 0) {
        $time .= ' ('.$row['entries'][$count]['points'].')';
    }
    return $time;
  }, 18, array(), array('class' => 'small'));
}
$countTable
->col('Bestzeit', 'bestTime', 10, array(), array('class' => 'small'))
->col('Teil.', 'part', 7, array(), array('class' => 'small'))
->col('Gesamtzeit', 'time', 10, array(), array('class' => 'small'))
->col('Punkte', 'points', 7, array(), array('class' => 'small'))
->col('', function ($row) use ($sex) { 
  $person = Import::getPersons($row['name'], $row['firstname'], $sex);
  return Link::person($person[0]['id']); 
}, 9);

echo $countTable;