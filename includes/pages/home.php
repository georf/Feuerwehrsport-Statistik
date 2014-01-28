<?php
Title::set('Überblick');

$persons = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `persons`
", 'count');

$scores = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores`
    WHERE `time` IS NOT NULL
", 'count');

$scores2 = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `scores`
    WHERE `time` IS NULL
", 'count');

$places = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `places`
", 'count');

$events = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `events`
", 'count');

$competitions = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `competitions`
", 'count');

$teams = $db->getFirstRow("
    SELECT COUNT(*) AS `count`
    FROM `teams`
", 'count');

$missed = $db->getRows("
    SELECT `missed`
    FROM `competitions`
");

$missedArr = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0);

foreach ($missed as $m) {

    $arr = explode(',', $m['missed']);
    $arr2 = array();
    foreach ($arr as $a) {
        if (trim($a) != '') {
            $arr2[] = trim($a);
        }
    }
    $count = count($config['missed']) - count($arr2);
    $missedArr[$count]++;
}



?>


<div class="four columns">
    <img src="/styling/images/statistiken-logo.png" alt="Logo" />
</div>
<div class="twelve columns">

<div class="toc right"><h5>Inhaltsverzeichnis</h5><ol>
    <li class="toc-placeholder">&nbsp;</li>
</ol></div>
    <h1>Feuerwehrsport - die große Auswertung</h1>
    <p>Diese Website dient der Auswertung des Feuerwehrsports in Deutschland
    über den Zeitraum der letzten Jahre. Dabei werden die Disziplinen
    <a href="http://de.wikipedia.org/wiki/Hakenleitersteigen">Hakenleitersteigen</a> (Männer),
    <a href="http://de.wikipedia.org/wiki/100-Meter-Hindernislauf">100-Meter-Hindernisbahn</a> (Frauen und Männer),
    <a href="http://de.wikipedia.org/wiki/Gruppenstafette">Gruppenstafette</a> (Frauen),
    <a href="http://de.wikipedia.org/wiki/Feuerwehrstafette">4x100-Meter-Feuerwehrstafette</a> (Frauen und Männer) und
    <a href="http://de.wikipedia.org/wiki/Löschangriff_Nass">Löschangriff Nass</a> (Frauen und Männer) ausgewertet.</p>
</div>
<h2 class="toToc">Überblick</h2>
<div class="sixteen columns clearfix">
    <div class="eight columns">
        <p>Die Datenbank besteht derzeit aus vielen Tabellen die mit Hilfe von verschiedenen Algorithmen verbunden und ausgewertet werden. In der rechts angeordneten Tabelle ist die Größenordnung der derzeitigen Erfassung aufgelistet.</p>
        <h4>Personen</h4>
        <p>Als Identifikator wird der Name genommen. Sollten sich zwei Sportler mit gleichen Namen im System befinden, bitte <a href="#kontakt">melden</a>.</p>
        <h4>Events und Wettkämpfe</h4>
        <p>Im System wird zwischen <a href="/page-events.html">Events</a> (D-Cup, DM, WM) und <a href="/page-competitions.html">Wettkämpfen</a> (DM 2012 in Cottbus oder D-Cup 2012 in Tüttleben) unterschieden. Dazu werden die <a href="/page-places.html">Wettkampforte</a> gespeichert, damit auch die Zeiten auf Tartanbahn mit denen auf Schotterbahnen verglichen werden können.</p>
    </div>
    <div class="four columns">
        <table class="table">
            <tr><th>Personen:</th><td><?=$persons?></td></tr>
            <tr><th>Zeiten:</th><td><?=$scores?></td></tr>
            <tr><th>Fehlversuche:</th><td><?=$scores2?></td></tr>
            <tr><th>Orte:</th><td><?=$places?></td></tr>
            <tr><th>Events:</th><td><?=$events?></td></tr>
            <tr><th>Wettkämpfe:</th><td><?=$competitions?></td></tr>
            <tr><th>Teams:</th><td><?=$teams?></td></tr>
        </table>
    </div>
    <div class="three columns">
        <h4>Verteilung der Zeiten</h4>
        <?=Chart::img('disciplines', false, true, 'count')?>
        <h4 style="margin-top:15px">Ø Beste 5 pro Wettkampf</h4>
        <?=Chart::img('overview_best', false, true, 'overview_best')?>
    </div>
</div>
<h2 class="toToc">Super Leistungen vom Jahr 2013</h2>
<?php

$_year = 2013;

$disciplines = array(
    array('hb', 'female'),
    array('hb', 'male'),
    array('hl', 'male'),
);

foreach ($disciplines as $d) {
    $dis = $d[0];
    $sex = $d[1];

    $persons = array();

    if ($dis == 'hl') echo '<br class="clear"/>';

    echo
    '<div class="eight columns">',
    '<table class="table" style="width:100%;box-shadow:5px 5px 5px #BFBFBF;">',
    '<tr><th colspan="3" style="background:#ADD8E6;text-align:center;border-color:#ADD8E6">',FSS::dis2img($dis),' ',FSS::dis2name($dis),' ',FSS::sex($sex),'</th></tr>';

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
        echo '<tr style="border-top:5px solid #ADD8E6"><td>',$i,'.</td><td>',Link::fullPerson($pid),'</td>';
        $i++;

        $ss = array();

        foreach ($p['scores'] as $s) {
            $ss[] = Link::competition($s['competition_id'], FSS::time($s['time']), $s['event']);
        }

        echo '<td style="font-size:0.9em">'.implode(', ', $ss).'</td></tr><tr>';

        echo '<td colspan="2"><em>',round($p['calc']/10).' Punkte</em></td><td>Durchschnitt: <strong>'.FSS::time($p['avg']).'</strong></td>';

        echo '</tr>';

        if ($i > 5) break;
    }

    echo '</table></div>';
}


echo
'<div class="eight columns">',
'<table class="table" style="width:100%;box-shadow:5px 5px 5px #BFBFBF;">',
'<tr><th colspan="3" style="background:#ADD8E6;text-align:center;border-color:#ADD8E6">',FSS::dis2img('gs'),' ',FSS::dis2name('gs'),'</th></tr>';


$scores = $db->getRows("
    SELECT `s`.*, `e`.`name` AS `event`
    FROM `scores_gs` `s`
    INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
    INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
    WHERE YEAR(`c`.`date`) = '".$db->escape($_year)."'
");
$teams = array();
foreach ($scores as $s) {
    if (!isset($teams[$s['team_id'].'-'.$s['team_number']])) {
        $teams[$s['team_id'].'-'.$s['team_number']] = array(
            'scores' => array(),
            'avg' => FSS::INVALID,
            'calc' => FSS::INVALID,
            'count' => 0,
            'invalids' => 0
        );
    }
    $teams[$s['team_id'].'-'.$s['team_number']]['scores'][] = $s;
}

foreach ($teams as $pid => $p) {
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
        $teams[$pid]['avg'] = $sum/$count;
    }

    //- 1/23 *x^2+ 10
    $sum = 0;
    for ($z = 0; $z < $count; $z++) {
        $s = -1/23 * pow($z, 2) + 10;
        if ($s < 0) break;
        $sum += $s;
    }
    $teams[$pid]['calc'] = $teams[$pid]['avg'] + $Ds*15 - $sum;
    //$persons[$pid]['calc'] = $persons[$pid]['avg'] + $Ds*15 - $count*10;

    $teams[$pid]['count'] = $count;
    $teams[$pid]['invalid'] = $Ds;

}

uasort($teams, function($a, $b) {
    return ($a['calc'] > $b['calc']);
});

$i = 1;
foreach ($teams as $pidn => $p) {
    $e = explode('-', $pidn);
    $pid = $e[0];
    echo '<tr style="border-top:5px solid #ADD8E6"><td>',$i,'.</td><td>',Link::team($pid),'</td>';
    $i++;

    $ss = array();

    foreach ($p['scores'] as $s) {
        $ss[] = Link::competition($s['competition_id'], FSS::time($s['time']), $s['event']);
    }

    echo '<td>'.implode(', ', $ss).'</td></tr><tr>';

    echo '<td colspan="2"><em>',round($p['calc']/10).' Punkte</em></td><td>Durchschnitt: <strong>'.FSS::time($p['avg']).'</strong></td>';

    echo '</tr>';

    if ($i > 5) break;
}

echo '</table></div>';

echo '<br class="clear"/>';

$sexes = array('female', 'male');
foreach ($sexes as $sex) {

    echo
    '<div class="eight columns">',
    '<table class="table" style="width:100%;box-shadow:5px 5px 5px #BFBFBF;">',
    '<tr><th colspan="3" style="background:#ADD8E6;text-align:center;border-color:#ADD8E6">',FSS::dis2img('la'),' ',FSS::dis2name('la'),' ',FSS::sex($sex),'</th></tr>';


    $scores = $db->getRows("
        SELECT `s`.*, `e`.`name` AS `event`
        FROM `scores_la` `s`
        INNER JOIN `competitions` `c` ON `c`.`id` = `s`.`competition_id`
        INNER JOIN `events` `e` ON `e`.`id` = `c`.`event_id`
        WHERE YEAR(`c`.`date`) = '".$db->escape($_year)."'
        AND `sex` = '".$sex."'
    ");
    $teams = array();
    foreach ($scores as $s) {
        if (!isset($teams[$s['team_id'].'-'.$s['team_number']])) {
            $teams[$s['team_id'].'-'.$s['team_number']] = array(
                'scores' => array(),
                'avg' => FSS::INVALID,
                'calc' => FSS::INVALID,
                'count' => 0,
                'invalids' => 0
            );
        }
        $teams[$s['team_id'].'-'.$s['team_number']]['scores'][] = $s;
    }

    foreach ($teams as $pid => $p) {
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
            $teams[$pid]['avg'] = $sum/$count;
        }

        //- 1/23 *x^2+ 10
        $sum = 0;
        for ($z = 0; $z < $count; $z++) {
            $s = -1/23 * pow($z, 2) + 10;
            if ($s < 0) break;
            $sum += $s;
        }
        $teams[$pid]['calc'] = $teams[$pid]['avg'] + $Ds*15 - $sum;
        //$persons[$pid]['calc'] = $persons[$pid]['avg'] + $Ds*15 - $count*10;

        $teams[$pid]['count'] = $count;
        $teams[$pid]['invalid'] = $Ds;

    }

    uasort($teams, function($a, $b) {
        return ($a['calc'] > $b['calc']);
    });

    $i = 1;
    foreach ($teams as $pidn => $p) {
        $e = explode('-', $pidn);
        $pid = $e[0];
        echo '<tr style="border-top:5px solid #ADD8E6"><td>',$i,'.</td><td>',Link::team($pid),'</td>';
        $i++;

        $ss = array();

        foreach ($p['scores'] as $s) {
            $ss[] = Link::competition($s['competition_id'], FSS::time($s['time']), $s['event']);
        }

        echo '<td style="font-size:0.9em">'.implode(', ', $ss).'</td></tr><tr>';

        echo '<td colspan="2"><em>',round($p['calc']/10).' Punkte</em></td><td>Durchschnitt: <strong>'.FSS::time($p['avg']).'</strong></td>';

        echo '</tr>';

        if ($i > 5) break;
    }
    echo '</table></div>';
}

echo '<br class="clear"/>';

echo '<div class="six columns">';
echo '<p>Zu '.Link::years('jedem Jahr').' können sich die Statistiken auch separat angesehen werden. Diese Statistiken wird auch in Zukunft ausgeweitet.</p>';
echo '</div>';
echo '<div class="seven columns">';
echo '<table style="width:100%">';
$years = $db->getRows("
    SELECT YEAR(`date`) AS `year`, COUNT(`id`) AS `count`
    FROM `competitions`
    GROUP BY `year`
    ORDER BY `year` DESC
");
for ( $i = 0; $i < count($years); $i = $i+4) {
    echo '<tr><td>'.Link::year($years[$i]['year']).' ('.$years[$i]['count'].')</td>';
    if (isset($years[$i+1])) echo '<td>'.Link::year($years[$i+1]['year']).' ('.$years[$i+1]['count'].')</td>';
    if (isset($years[$i+2])) echo '<td>'.Link::year($years[$i+2]['year']).' ('.$years[$i+2]['count'].')</td>';
    if (isset($years[$i+3])) echo '<td>'.Link::year($years[$i+3]['year']).' ('.$years[$i+3]['count'].')</td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';

?>

<h2 class="toToc">Mitmachen</h2>
<div class="sixteen columns clearfix">
    <div class="nine columns">
        <p>Jeder kann bei der Vervollständigung der Daten mitmachen. Dafür gibt es mehrere Möglichkeiten:</p>
        <ul class="disc">
            <li>Zeiten einer Mannschaft zuordnen<a class="helpinfo" data-file="mannschafzuordnen">&nbsp;</a></li>
            <li>Link zu Wettkampf melden<a class="helpinfo" data-file="linkzuwettkampf">&nbsp;</a></li>
            <li>Person einer Mannschaft zuordnen<a class="helpinfo" data-file="personzuordnen">&nbsp;</a></li>
            <li><a href="#fehler">Fehler melden</a></li>
        </ul>
    </div>
    <div class="six columns" style="text-align:center;"><img src="/styling/images/system-users.png" alt=""/></div>
</div>

<h2 class="toToc">Datenbank</h2>
<div class="sixteen columns clearfix">
    <div class="nine columns">
        <p>Die Daten für diese statistische Auswertung stammen von verschiedenen Quellen. Ein großer Teil wurde mir von Daniel Grosche zur Verfügung gestellt. Diese Daten lagen sogar im Excel-Format vor, wodurch die Daten schnell importiert werden konnten. Weitere Ergebnisse wurden aus PDFs extrahiert. Dabei ist neben der Seite vom Team MV die Seite des LFV BB und der Feuerwehr Cottbus zu nennen, die viele Ergebnisse bereitstellen.</p>
        <p>Ein großer Dank geht auch an <a href="http://www.feuerwehrsport-statistik.de/page-person-302.html">Florian Müller</a>. Er hat vielen Zeiten Mannschaften zugeordnet und mehrere Wettkämpfe digitalisiert.</p>
        <p>Sollte sich jemand durch die Verwendung der Daten in seinem Urheberrecht verletzt fühlen, bitte nicht sofort abmahnen. Über <a href="#kontakt">E-Mail</a> bin ich immer relativ schnell erreichbar. Das gleiche gilt auch für <a href="#fehler">Fehler in den Daten</a>.</p>
        <p>Falls auch ein Informatiker unter den Lesern sein sollte:</p>
        <ul class="disc">
            <li><a href="https://github.com/georf/Feuerwehrsport-Statistik">Quelltext</a></li>
            <li><a href="https://github.com/georf/Feuerwehrsport-Statistik-Daten">Datenbank</a></li>
            <li><a href="https://github.com/georf/Feuerwehrsport-Statistik-Ergebnisse">PDF-Dokumente</a></li>
            <li><a href="https://github.com/georf/Feuerwehrsport-Statistik-Logos">Logos der Teams</a></li>
        </ul></p>
    </div>
    <div class="six columns" style="text-align:center;"><img src="/styling/images/application-x-gnumeric.png" alt=""/></div>
</div>
<div class="sixteen columns">
        <h4>Status des Imports</h4>
        <div style="width:910px;margin:5px auto 15px auto;">
        <?php

            $co = array(
                '#009200',
                '#00C600',
                '#60F20E',
                '#FFEB00',
                '#FF8100',
                '#E73131'
            );

            foreach ($missedArr as $key => $count) {

                $c = $count/$competitions;

                $t = floor($c*100).'%';
                if ($c*100 < 10) $t = ' ';

                echo '<div style="text-align:center;float:right;height:20px;background:'.$co[$key].';position:relative;width:'.floor($c*900).'px;color:#'.dechex(0xFFFFFF-hexdec(substr($co[$key],1))).'" title="'.floor($c*100).'% ('.$count.' Wettkämpfe)">'.$t.'</div>';
            }
        ?>
    </div>
</div>
<h2 class="toToc" id="fehler">Fehler in den Daten</h2>
<div class="sixteen columns clearfix">
    <div class="nine columns">
        <p>Die Daten stammen von unterschiedlichen Quellen, die alle separat importiert wurden. Dabei kommt es immerwieder zu Fehlern, weil Namen verkehrt zugeordnet werden oder Zeiten verschoben sind. Solltet ihr einen solchen Fehler gefunden haben, schickt mir einfach eine <a href="#kontakt">E-Mail</a>.</p>
        <p>Außerdem gibt es die Möglichkeit, Fehler direkt zu beheben. Viele Namen wurden in den Ergebnislisten falsch geschrieben. Deshalb sind diese Namen in der Datenbank doppelt vorhanden. Um dies zu beheben, geht man auf die Seite der nicht korrekten Person. Am unteren Ende der Seite ist dann eine Schaltfläche für das Beheben dieses Fehlers.</p>
        <p>Viel interessanter für mich und hoffentlich auch für euch sind zusätzliche Daten. Falls ihr also noch eine Ergebnisliste eines Wettkampfes habt, dann <a href="#kontakt">schickt</a> ihn mir doch bitte. Ich pflege die Daten dann in die Datenbank ein und die Statistiken werden erweitert. Am besten sind dafür natürlich digitale Daten geeignet. Eingescannte Ergebnislisten lassen sich nicht importieren und müssten abgeschrieben werden. Diese Arbeit werde ich mir nicht machen, aber falls ihr unbedingt einen Wettkampf mit drin haben wollt, den es nur noch mit eingescannter Liste gibt, dann schreibt ihn ab und schickt ihn mir. Außerdem würde ich dann gerne noch einen Link als Beweis für die Echtheit haben.</p>
    </div>
    <div class="six columns" style="text-align:center;"><img src="/styling/images/applications-education.png" alt=""/></div>
</div>
<h2 class="toToc" id="kontakt">Kontakt</h2>
<div class="sixteen columns clearfix">
    <div class="nine columns">
        <p>Besteht ein Bedürfnis, den Administrator und Entwickler dieser Seite zu kontaktieren, ist dies sehr wohl gewünscht. Für Anregungen oder Kritik bin ich immer offen. Meine E-Mail-Adresse im Bezug auf diese Seite lautet <em><a href="mailto:statistikseite@feuerwehrsport-teammv.de">statistikseite@feuerwehrsport-teammv.de</a></em>.</p>
        <p>Falls es sich um einen Fehler in der Programmierung handelt, bitte ich darum, dass die URL der Seite kopiert wird und mit der E-Mail verschickt wird. So kann ich den Fehler schneller finden und auch beheben.</p>
    </div>
    <div class="six columns" style="text-align:center;"><img src="/styling/images/kontact.png" alt=""/></div>
</div>
