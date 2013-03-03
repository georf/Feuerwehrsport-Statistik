<?php


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
<h2 class="toToc">Überblick</h2>
<div class="sixteen columns clearfix">
    <div class="eight columns">
        <p>Die Datenbank besteht derzeit aus vielen Tabellen die mit Hilfe von verschiedenen Algorithmen verbunden und ausgewertet werden. In der rechts angeordneten Tabelle ist die Größenordnung der derzeitigen Erfassung aufgelistet.</p>
        <h4>Personen</h4>
        <p>Als Identifikator wird der Name genommen. Sollten sich zwei Sportler mit gleichen Namen im System befinden, bitte <a href="#kontakt">melden</a>.</p>
        <h4>Events und Wettkämpfe</h4>
        <p>Im System wird zwischen <a href="?page=events">Events</a> (D-Cup, DM, WM) und <a href="?page=competitions">Wettkämpfen</a> (DM 2012 in Cottbus oder D-Cup 2012 in Tüttleben) unterschieden. Dazu werden die <a href="?page=places">Wettkampforte</a> gespeichert, damit auch die Zeiten auf Tartanbahn mit denen auf Schotterbahnen verglichen werden können.</p>
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
        <img src="chart.php?type=disciplines" alt="" class="infochart" data-file="count"/>
        <h4 style="margin-top:15px">Ø Beste 5 pro Wettkampf</h4>
        <img src="chart.php?type=overview_best" alt="" class="infochart" data-file="overview_best"/>
    </div>
    <div class="sixteen columns">
        <img src="chart.php?type=count" alt="" class="infochart" data-file="anzahlwettkampf"/>
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
</div>
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
    <div class="six columns" style="text-align:center;"><img src="styling/images/system-users.png" alt=""/></div>
</div>

<h2 class="toToc">Datenbank</h2>
<div class="sixteen columns clearfix">
    <div class="nine columns">
        <p>Die Daten für diese statistische Auswertung stammen von verschiedenen Quellen. Ein großer Teil wurde mir von Daniel Grosche zur Verfügung gestellt. Diese Daten lagen sogar im Excel-Format vor, wodurch die Daten schnell importiert werden konnten. Weitere Ergebnisse wurden aus PDFs extrahiert. Dabei ist neben der Seite vom Team MV die Seite des LFV BB und der Feuerwehr Cottbus zu nennen, die viele Ergebnisse bereitstellen.</p>
        <p>Ein großer Dank geht auch an <a href="http://www.feuerwehrsport-statistik.de/?page=person&id=302">Florian Müller</a>. Er hat vielen Zeiten Mannschaften zugeordnet und mehrere Wettkämpfe digitalisiert.</p>
        <p>Sollte sich jemand durch die Verwendung der Daten in seinem Urheberrecht verletzt fühlen, bitte nicht sofort abmahnen. Über <a href="#kontakt">E-Mail</a> bin ich immer relativ schnell erreichbar. Das gleiche gilt auch für <a href="#fehler">Fehler in den Daten</a>.</p>
        <p>Falls auch ein Informatiker unter den Lesern sein sollte: Ich stelle natürlich die Datensammlung auch gerne zur Verfügung, falls jemand selber damit rumprobieren will. Der Quelltext für die Programmierung ist noch nicht öffentlich einsehbar. Sobald ich diesen aufgeräumt habe, wird er bei Github zur Verfügung gestellt.</p>
    </div>
    <div class="six columns" style="text-align:center;"><img src="styling/images/application-x-gnumeric.png" alt=""/></div>
</div>
<h2 class="toToc" id="fehler">Fehler in den Daten</h2>
<div class="sixteen columns clearfix">
    <div class="nine columns">
        <p>Die Daten stammen von unterschiedlichen Quellen, die alle separat importiert wurden. Dabei kommt es immerwieder zu Fehlern, weil Namen verkehrt zugeordnet werden oder Zeiten verschoben sind. Solltet ihr einen solchen Fehler gefunden haben, schickt mir einfach eine <a href="#kontakt">E-Mail</a>.</p>
        <p>Außerdem gibt es die Möglichkeit, Fehler direkt zu beheben. Viele Namen wurden in den Ergebnislisten falsch geschrieben. Deshalb sind diese Namen in der Datenbank doppelt vorhanden. Um dies zu beheben, geht man auf die Seite der nicht korrekten Person. Am unteren Ende der Seite ist dann eine Schaltfläche für das Beheben dieses Fehlers.</p>
        <p>Viel interessanter für mich und hoffentlich auch für euch sind zusätzliche Daten. Falls ihr also noch eine Ergebnisliste eines Wettkampfes habt, dann <a href="#kontakt">schickt</a> ihn mir doch bitte. Ich pflege die Daten dann in die Datenbank ein und die Statistiken werden erweitert. Am besten sind dafür natürlich digitale Daten geeignet. Eingescannte Ergebnislisten lassen sich nicht importieren und müssten abgeschrieben werden. Diese Arbeit werde ich mir nicht machen, aber falls ihr unbedingt einen Wettkampf mit drin haben wollt, den es nur noch mit eingescannter Liste gibt, dann schreibt ihn ab und schickt ihn mir. Außerdem würde ich dann gerne noch einen Link als Beweis für die Echtheit haben.</p>
    </div>
    <div class="six columns" style="text-align:center;"><img src="styling/images/applications-education.png" alt=""/></div>
</div>
<h2 class="toToc" id="kontakt">Kontakt</h2>
<div class="sixteen columns clearfix">
    <div class="nine columns">
        <p>Besteht ein Bedürfnis, den Administrator und Entwickler dieser Seite zu kontaktieren, ist dies sehr wohl gewünscht. Für Anregungen oder Kritik bin ich immer offen. Meine E-Mail-Adresse im Bezug auf diese Seite lautet <em><a href="mailto:statistikseite@feuerwehrsport-teammv.de">statistikseite@feuerwehrsport-teammv.de</a></em>.</p>
        <p>Falls es sich um einen Fehler in der Programmierung handelt, bitte ich darum, dass die URL der Seite kopiert wird und mit der E-Mail verschickt wird. So kann ich den Fehler schneller finden und auch beheben.</p>
    </div>
    <div class="six columns" style="text-align:center;"><img src="styling/images/kontact.png" alt=""/></div>
</div>
