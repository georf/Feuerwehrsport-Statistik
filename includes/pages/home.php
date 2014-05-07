<?php
Title::set('Überblick');
echo Bootstrap::row()
  ->col('<img src="/styling/images/statistiken-logo.png" alt="Logo" />', 3)
  ->col(Title::h1('Feuerwehrsport - die große Auswertung'), 9);

echo Bootstrap::row()
  ->col('<p class="lead">Diese Website dient der Auswertung des Feuerwehrsports in Deutschland über den Zeitraum der letzten Jahre. Dabei werden die Disziplinen
    <a href="http://de.wikipedia.org/wiki/Hakenleitersteigen">Hakenleitersteigen</a> (Männer),
    <a href="http://de.wikipedia.org/wiki/100-Meter-Hindernislauf">100-Meter-Hindernisbahn</a> (Frauen und Männer),
    <a href="http://de.wikipedia.org/wiki/Gruppenstafette">Gruppenstafette</a> (Frauen),
    <a href="http://de.wikipedia.org/wiki/Feuerwehrstafette">4x100-Meter-Feuerwehrstafette</a> (Frauen und Männer) und
    <a href="http://de.wikipedia.org/wiki/Löschangriff_Nass">Löschangriff Nass</a> (Frauen und Männer) ausgewertet.</p>
', 9)
  ->col(TableOfContents::get()
    ->link('overview', 'Überblick')
    ->link('year2014', 'Jahr 2014', 'Super Leistungen vom Jahr 2014')
    ->link('mitmachen', 'Mitmachen')
    ->link('datenbank', 'Datenbank')
    ->link('fehler', 'Fehler melden', 'Fehler in den Daten melden')
    ->link('kontakt', 'Kontakt', 'Kontakt aufnehmen')
  , 3);

echo Title::h2('Überblick', 'overview');

echo Bootstrap::row()
  ->col(
    '<p>Die Datenbank besteht derzeit aus vielen Tabellen die mit Hilfe von verschiedenen Algorithmen verbunden und ausgewertet werden. In der rechts angeordneten Tabelle ist die Größenordnung der derzeitigen Erfassung aufgelistet.</p>'.
    '<h4>Personen</h4>'.
    '<p>Als Identifikator wird der Name genommen. Sollten sich zwei Sportler mit gleichen Namen im System befinden, bitte <a href="#kontakt">melden</a>.</p>'.
    '<h4>Events und Wettkämpfe</h4>'.
    '<p>Im System wird zwischen '.Link::events().' (D-Cup, DM, WM) und '.Link::competitions("Wettkämpfen").' (DM 2012 in Cottbus oder D-Cup 2012 in Tüttleben) unterschieden. Dazu werden die '.Link::places().' gespeichert, damit auch die Zeiten auf Tartanbahn mit denen auf Schotterbahnen verglichen werden können.</p>'
  , 6)
  ->col(
    '<table class="table">'.
      '<tr><th>Personen:</th><td>'.CountStatistics::persons().'</td></tr>'.
      '<tr><th>Zeiten:</th><td>'.CountStatistics::scores().'</td></tr>'.
      '<tr><th>Fehlversuche:</th><td>'.CountStatistics::scores2().'</td></tr>'.
      '<tr><th>Orte:</th><td>'.CountStatistics::places().'</td></tr>'.
      '<tr><th>Events:</th><td>'.CountStatistics::events().'</td></tr>'.
      '<tr><th>Wettkämpfe:</th><td>'.CountStatistics::competitions().'</td></tr>'.
      '<tr><th>Teams:</th><td>'.CountStatistics::teams().'</td></tr>'.
    '</table>'
  , 3)
  ->col(
    '<h4>Verteilung der Zeiten</h4>'.
    Chart::img('disciplines', false, true, 'count').
    '<h4 style="margin-top:15px">Ø Beste 5 pro Wettkampf</h4>'.
    Chart::img('overview_best', false, true, 'overview_best')
  , 3);

echo Title::h2('Super Leistungen vom Jahr 2014', 'year2014');

$navTab = Bootstrap::navTab('best-of-year-home');
$year = 2014;
$disciplines = array(
  array('hb', false, 'female'),
  array('hb', false, 'male'),
  array('hl', false, 'male'),
  array('gs', true,  false),
  array('la', true,  'female'),
  array('la', true,  'male'),
);

foreach ($disciplines as $d) {
  $discipline = $d[0];
  $group      = $d[1];
  $sex        = $d[2];

  if ($group) {
    $best = Statistics::calculateTeams($year, $discipline, $sex);
  } else {
    $best = Statistics::calculatePersons($year, $discipline, $sex);
  }

  $output = '<table class="table">';
  $i = 0;
  foreach ($best as $id => $item) {
    $i++;
    $output .= '<tr><td>'.$i.'.</td><td>';
    $output .= ($group)? Link::team($id) : Link::fullPerson($id);
    $output .= '</td>';

    $ss = array();
    foreach ($item['scores'] as $s) {
      $ss[] = Link::competition($s['competition_id'], FSS::time($s['time']), $s['event']);
    }

    $output .= '<td>'.implode(', ', $ss).'</td></tr>';
    $output .= '<tr class="hint-line">';
    $output .= '<td colspan="2"><em>'.round($item['calc']/10).' Punkte</em></td>';
    $output .= '<td>Durchschnitt: <strong>'.FSS::time($item['avg']).'</strong></td>';
    $output .= '</tr>';
    if ($i > 4) break;
  }
  $output .= '</table>';
  $headline = FSS::dis2img($discipline).' '.strtoupper($discipline);
  if ($sex) $headline .= ' '.FSS::sex($sex);
  $navTab->tab($headline, $output, FSS::dis2name($discipline));
}

echo $navTab;

$years = $db->getRows("
  SELECT YEAR(`date`) AS `year`, COUNT(`id`) AS `count`
  FROM `competitions`
  GROUP BY `year`
  ORDER BY `year` DESC
");
$output = '';
for ( $i = 0; $i < count($years); $i = $i+4) {
  $output .= '<tr><td>'.Link::year($years[$i]['year']).' ('.$years[$i]['count'].')</td>';
  if (isset($years[$i+1])) $output .= '<td>'.Link::year($years[$i+1]['year']).' ('.$years[$i+1]['count'].')</td>';
  if (isset($years[$i+2])) $output .= '<td>'.Link::year($years[$i+2]['year']).' ('.$years[$i+2]['count'].')</td>';
  if (isset($years[$i+3])) $output .= '<td>'.Link::year($years[$i+3]['year']).' ('.$years[$i+3]['count'].')</td>';
    $output .= '</tr>';
}
echo Bootstrap::row()
  ->col('<p>Zu '.Link::years('jedem Jahr').' können sich die Statistiken auch separat angesehen werden. Diese Statistiken wird auch in Zukunft ausgeweitet.</p>', 5)
  ->col('<table class="years-overview-home">'.$output.'</table>', 7);

echo Title::h2('Mitmachen', 'mitmachen');
echo Bootstrap::row()
  ->col(
    '<p>Jeder kann bei der Vervollständigung der Daten mitmachen. Dafür gibt es mehrere Möglichkeiten:</p>'.
    '<ul>'.
      '<li>Zeiten einer Mannschaft zuordnen</li>'.
      '<li>Link zu Wettkampf melden</li>'.
      '<li>Person einer Mannschaft zuordnen</li>'.
      '<li><a href="#fehler">Fehler melden</a></li>'.
    '</ul>', 9)
  ->col('<img src="/styling/images/system-users.png" alt=""/>', 3);

echo Title::h2('Datenbank', 'datenbank');
echo Bootstrap::row()
  ->col(
    '<p>Die Daten für diese statistische Auswertung stammen von verschiedenen Quellen. Ein großer Teil wurde mir von Daniel Grosche zur Verfügung gestellt. Diese Daten lagen sogar im Excel-Format vor, wodurch die Daten schnell importiert werden konnten. Weitere Ergebnisse wurden aus PDFs extrahiert. Dabei ist neben der Seite vom Team MV die Seite des LFV BB und der Feuerwehr Cottbus zu nennen, die viele Ergebnisse bereitstellen.</p>'.
    '<p>Ein großer Dank geht auch an <a href="http://www.feuerwehrsport-statistik.de/page-person-302.html">Florian Müller</a>. Er hat vielen Zeiten Mannschaften zugeordnet und mehrere Wettkämpfe digitalisiert.</p>'.
    '<p>Sollte sich jemand durch die Verwendung der Daten in seinem Urheberrecht verletzt fühlen, bitte nicht sofort abmahnen. Über <a href="#kontakt">E-Mail</a> bin ich immer relativ schnell erreichbar. Das gleiche gilt auch für <a href="#fehler">Fehler in den Daten</a>.</p>'.
    '<p>Falls auch ein Informatiker unter den Lesern sein sollte:</p>'.
    '<ul class="disc">'.
      '<li><a href="https://github.com/georf/Feuerwehrsport-Statistik">Quelltext</a></li>'.
      '<li><a href="https://github.com/georf/Feuerwehrsport-Statistik-Daten">Datenbank</a></li>'.
      '<li><a href="https://github.com/georf/Feuerwehrsport-Statistik-Ergebnisse">PDF-Dokumente</a></li>'.
      '<li><a href="https://github.com/georf/Feuerwehrsport-Statistik-Logos">Logos der Teams</a></li>'.
    '</ul>', 9)
  ->col('<img src="/styling/images/application-x-gnumeric.png" alt=""/>', 3);

echo Title::h2('Fehler in den Daten', 'fehler');
echo Bootstrap::row()
  ->col(
    '<p>Die Daten stammen von unterschiedlichen Quellen, die alle separat importiert wurden. Dabei kommt es immerwieder zu Fehlern, weil Namen verkehrt zugeordnet werden oder Zeiten verschoben sind. Solltet ihr einen solchen Fehler gefunden haben, schickt mir einfach eine <a href="#kontakt">E-Mail</a>.</p>'.
    '<p>Außerdem gibt es die Möglichkeit, Fehler direkt zu beheben. Viele Namen wurden in den Ergebnislisten falsch geschrieben. Deshalb sind diese Namen in der Datenbank doppelt vorhanden. Um dies zu beheben, geht man auf die Seite der nicht korrekten Person. Am unteren Ende der Seite ist dann eine Schaltfläche für das Beheben dieses Fehlers.</p>'.
    '<p>Viel interessanter für mich und hoffentlich auch für euch sind zusätzliche Daten. Falls ihr also noch eine Ergebnisliste eines Wettkampfes habt, dann <a href="#kontakt">schickt</a> ihn mir doch bitte. Ich pflege die Daten dann in die Datenbank ein und die Statistiken werden erweitert. Am besten sind dafür natürlich digitale Daten geeignet. Eingescannte Ergebnislisten lassen sich nicht importieren und müssten abgeschrieben werden. Diese Arbeit werde ich mir nicht machen, aber falls ihr unbedingt einen Wettkampf mit drin haben wollt, den es nur noch mit eingescannter Liste gibt, dann schreibt ihn ab und schickt ihn mir. Außerdem würde ich dann gerne noch einen Link als Beweis für die Echtheit haben.</p>'
    , 9)
  ->col('<img src="/styling/images/applications-education.png" alt=""/>', 3);

echo Title::h2('Kontakt', 'kontakt');
echo Bootstrap::row()
  ->col(
    '<p>Besteht ein Bedürfnis, den Administrator und Entwickler dieser Seite zu kontaktieren, ist dies sehr wohl gewünscht. Für Anregungen oder Kritik bin ich immer offen. Meine E-Mail-Adresse im Bezug auf diese Seite lautet <em><a href="mailto:statistikseite@feuerwehrsport-teammv.de">statistikseite@feuerwehrsport-teammv.de</a></em>.</p>'.
    '<p>Falls es sich um einen Fehler in der Programmierung handelt, bitte ich darum, dass die URL der Seite kopiert wird und mit der E-Mail verschickt wird. So kann ich den Fehler schneller finden und auch beheben.</p>'
    , 9)
  ->col('<img src="/styling/images/kontact.png" alt=""/>', 3);