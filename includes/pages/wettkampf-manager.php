<?php

$versionsPath = $config['base']."wettkampf-manager/";
$vz = opendir($versionsPath);
$versions = array();
while ($versionDir = readdir($vz)) {
  if (is_dir($versionsPath.$versionDir) && $versionDir != "." && $versionDir != "..") {
    $versions[$versionDir] = array();
    $vzi = opendir($versionsPath.$versionDir);
    while ($target = readdir($vzi)) {
      if (is_file($versionsPath.$versionDir.'/'.$target)) {
        $versions[$versionDir][] = $target;
      }
    }
    sort($versions[$versionDir]);
  }
}
krsort($versions);

$first = true;
$versionOutput = '<div class="list-group">';
foreach($versions as $versionString => $targets) {
  $version = preg_replace("|_(.*)$|", "", $versionString);
  $date = preg_replace("|^(.*)_|", "", $versionString);
  $versionOutput .=
   '<a href="#" class="version-select list-group-item'.($first?' active':'').'" data-version="'.$version.'"><span class="badge">'.$date.'</span>'.$version.'</a>'.
   '<div data-version="'.$version.'" class="list-group-item'.($first?'':' hide').'"><ul>';
   
  foreach ($targets as $target) {
    $versionOutput .= '<li><a href="/wettkampf-manager/'.$versionString.'/'.$target.'">wettkampf-manager-'.$target.'</a></li>';
  }
  $versionOutput .= '</ul></div>';
  $first = false;
}
$versionOutput .= '</div>';


echo Bootstrap::row()
  ->col('<img src="/styling/images/wettkampf-manager.png" alt="" />', 3)
  ->col(Title::set('Wettkampf-Manager'), 9);

echo Bootstrap::row()
  ->col('<p class="lead">Der Wettkampf-Manager ist ein Programm zum Auswerten von Wettkämpfen. Unabhängig von Excel können Startlisten, Ergebnislisten und Gesamtwertungen erstellt werden. Die Eingabe der Daten ist über mehrere PCs gleichzeitig möglich. Eine Live-Tabelle kann über WLAN bereitgestellt werden.</p>
', 8)
  ->col(TableOfContents::get()
    ->link('overview', 'Überblick')
    ->link('download', 'Download')
    ->link('hinweise', 'Hinweise zur Installation')
    ->link('fehler', 'Fehler, Probleme, Wünsche')
    ->link('zukunft', 'Geplante Funktionen')
  , 4);


echo Bootstrap::row()
  ->col(
    Title::h2('Überblick', 'overview').
    '<p>Eine Auflistung der bisherigen Funktionen:'.
    '<ul>'.
      '<li>Namensvorschläge bei Eingabe</li>'.
      '<li>Unterstützte Disziplinen:'.
        '<ul>'.
          '<li>Löschangriff Nass</li>'.
          '<li>Gruppenstafette</li>'.
          '<li>4x100-Meter-Feuerwehrstafette</li>'.
          '<li>100-Meter-Hindernisbahn</li>'.
          '<li>Hakenleitersteigen</li>'.
        '</ul>'.
      '</li>'.
      '<li>Gesamtwertungen:'.
        '<ul>'.
          '<li>Plätze zu Punkte (Negativpunkte pro Disziplin)</li>'.
          '<li>D-Cup-Wertung</li>'.
        '</ul>'.
      '</li>'.
      '<li>automatische Zweikampfwertung</li>'.
      '<li>automatische U20-Wertung</li>'.
      '<li>D-Cup-Jahreswertung integriert</li>'.
      '<li>Startlistenerstellung:'.
        '<ul>'.
          '<li>Aus gewünschten Mannschaften</li>'.
          '<li>Aus gewünschten Personen auch nach Reihenfolge</li>'.
          '<li>Einzelstarter werden berücksichtigt</li>'.
          '<li>Nur Beste X aus Vorlauf (Finale)</li>'.
          '<li>Bahnwechsel</li>'.
          '<li>Anzahl der Bahnen kann angegeben werden</li>'.
        '</ul>'.
      '</li>'.
      '<li>Bedienung über Webbrowser:'.
        '<ul>'.
          '<li>Nutzer mit Passwort können Zeiten bearbeiten</li>'.
          '<li>Nutzer ohne Passwort können Lesen</li>'.
          '<li>Für mobile Endgeräte optimiert</li>'.
        '</ul>'.
      '</li>'.
    '</ul>'
  , 6)

// <ul>'.
//       '<li><a href="/wettkampf-manager/wettkampf-manager-windows.zip">Windows (64bit)</a></li>'.
//     '</ul>

  ->col(
    Title::h2('Download', 'download').
    Bootstrap::row()->col(
    $versionOutput, 12)
  , 6)
  ->col(
    Title::h2('Hinweise zur Installation', 'hinweise').
    '<ol>'.
      '<li>Datei herunterladen und entpacken</li>'.
      '<li><em>install.bat</em> ausführen</li>'.
      '<li>Warten bis im Terminal-Fenster <em>Weiter mit beliebiger Taste</em> steht. Dieser Vorgang benötigt eine Internetverbindung und dauert circa 15 Minuten.</li>'.
      '<li><em>start_server.bat</em> ausführen (hier fragt die Firewall ob <em>Ruby</em> Zugriff haben darf)</li>'.
      '<li><a href="http://localhost/">http://localhost/</a> im Webbrowser eingeben und angezeigten Schritten folgen</li>'.
    '</ol>'.
    '<p>Die Installation kann erneut durchgeführt werden. Dafür einfach erneut die <em>install.bat</em> ausführen. Dabei wird gefragt, ob die vorhandenen Daten gelöscht werden sollen.</p>'
  , 6)
  ->col(
    Title::h2('Fehler, Probleme, Wünsche', 'fehler').
    '<p>Es kann immer mal wieder vorkommen, dass Fehler auftreten. Diese bitte per E-Mail oder als Ticket melden. Vorher könnt ihr einmal nachgucken, ob es für das Problem schon ein Ticket gibt.</p>'.
    '<ul>'.
      '<li><a href="mailto:georf@georf.de">E-Mail schreiben</a></li>'.
      '<li><a href="https://github.com/Feuerwehrsport/wettkampf-manager/issues">Offene Tickets</a></li>'.
    '</ul>'.
    '<p>Es können natürlich auch Wünsche geäußert werden. Gerade wenn ihr einen besonderen Wettkampfmodus habt, der als Startkonfiguration verfügbar sein soll, ist das kein Problem.</p>'
  , 6)
  ->col(
    Title::h2('Geplante Funktionen', 'zukunft').
    '<ul>'.
      '<li>Automatisches Veröffentlichen auf der Statistikseite</li>'.
      '<li>Automatisches Veröffentlichen auf gesonderter Seite</li>'.
      '<li>Online-Anmeldung im Vorfeld</li>'.
      '<li>Viele Verbesserungen bei der Bedienung</li>'.
      '<li>Startnummern</li>'.
      '<li>Losreihenfolge</li>'.
    '</ul>'
  , 6);