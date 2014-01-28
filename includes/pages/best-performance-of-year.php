<?php
Title::set('Überblick');

if (!Check::get('id') || !preg_match('|^[1,2][0-9]{3}$|', $_GET['id'])) throw new PageNotFound();
$_year = $_GET['id'];

$disciplines = array(
    array('hb', 'female'),
    array('hb', 'male'),
    array('hl', 'male'),
);

echo '<h1>Übersicht der Einzeldisziplinen für das Jahr '.$_year.'</h1>';
echo '<div class="six columns">';
echo '<div class="toc"><h5>Inhaltsverzeichnis</h5><ol>';
foreach ($disciplines as $d) {
    echo '<li><a href="#'.$d[0].'-'.$d[1].'">',FSS::dis2name($d[0]),' ',FSS::sex($d[1]),'</a></li>';
}
echo '</ol></div><p><img src="/styling/images/formel.png" alt=""/></p></div>';
echo '<div class="seven columns">';
echo '<p>Die Tabellen zeigen die Leistungen der 50 besten Sportler im Jahr '.$_year.'. Für die Rangordnung ist nicht nur die Durchschnittszeit entscheidend. Zusätzlich werden die Strafpunkte erhöht, wenn man mehr ungültige Versuche hat und verringert, wenn man mehr Läufe absolviert hat. Somit ergibt sich ein Vergleich der konstanten Leistungen.</p>';
echo '<p>Die nebenstehende Formel zeigt die Berechnung für die Strafpunkte. Dabei sind<br/><em>g</em> = Anzahl gültiger Läufe<br/><em>u</em> = Anzahl ungültiger Läufe<br/><em>a</em> = <em>g</em> + <em>u</em><br/><em>t</em> = Zeiten der gültigen Läufe</p>';
echo '</div>';

//\frac { \sum _{ i=1 }^{ n_{ gültig } }{ t_{ i } }  }{ n_{ gültig } } +15n_{ ungültig }-10n_{ gesamt }
//\frac {  \frac { 1 }{ g } \sum _{ i=1 }^{ g }{ 100t_{ i } } +15u-\sum _{ i=0 }^{ a } \frac { -i^{ 2 } }{ 23 } +10}{10}
foreach ($disciplines as $d) {
    $dis = $d[0];
    $sex = $d[1];

    $persons = array();

    echo '<h2>',FSS::dis2name($dis),' ',FSS::sex($sex),'</h2>',
    '<div class="five columns">',FSS::dis2img($dis, 'blue'),
    '</div>',
    '<div class="nine columns">',
    '<table class="table" style="width:100%">';
    $scores = $db->getRows("
        SELECT `s`.*, `e`.`name` AS `event`
        FROM `scores` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
        INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
        WHERE YEAR(`c`.`date`) = '".$db->escape($_year)."'
        AND `discipline` = '".$dis."'
        AND `p`.`sex` = '".$sex."'
    ");

    foreach ($scores as $s) {
        if (!isset($persons[$s['person_id']])) {
            $persons[$s['person_id']] = array(
                'scores' => array(),
                'avg' => FSS::INVALID,
                'calc' => FSS::INVALID,
                'count' => 0,
                'invalids' => 0
            );
        }
        $persons[$s['person_id']]['scores'][] = $s;
    }

    foreach ($persons as $pid => $p) {
        $sum = 0;
        $Ds = 0;
        $count = 0;

        foreach ($p['scores'] as $s) {
            if (FSS::isInvalid($s['time'])) {
                $Ds++;
            } else {
                $sum += intval($s['time']);
                $count++;
            }
        }
        if ($count != 0) {
            $persons[$pid]['avg'] = $sum/$count;
        }

        //- 1/23 *x^2+ 10
        $sum = 0;
        for ($z = 0; $z < $count; $z++) {
            $s = -1/23 * pow($z, 2) + 10;
            if ($s < 0) break;
            $sum += $s;
        }
        $persons[$pid]['calc'] = $persons[$pid]['avg'] + $Ds*15 - $sum;
        //$persons[$pid]['calc'] = $persons[$pid]['avg'] + $Ds*15 - $count*10;

        $persons[$pid]['count'] = $count;
        $persons[$pid]['invalid'] = $Ds;

    }

    uasort($persons, function($a, $b) {
        return ($a['calc'] > $b['calc']);
    });

    $i = 1;
    foreach ($persons as $pid => $p) {
        echo '<tr style="border-top:5px solid #ADD8E6"><td rowspan="'.count($p['scores']).'">',$i,'.</td><td rowspan="'.count($p['scores']).'">',Link::fullPerson($pid),'</td>';
        $i++;

        foreach ($p['scores'] as $s) {
            echo '<td>'.FSS::time($s['time']).'</td><td>'.Link::competition($s['competition_id'], $s['event']).'</td></tr><tr>';
        }

        echo '<td colspan="2"><em>',round($p['calc']/10).' Punkte</em></td><td colspan="2">Durchschnitt: <strong>'.FSS::time($p['avg']).'</strong></td>';

        echo '</tr>';

        if ($i > 50) break;
    }

    echo '</table></div>';
}
