<?php
echo Title::set('RSS-Feeds');
?>
<p>Diese Website stellt mehrere Feeds zur Verfügung. Dabei sind alle Feeds in mehreren Formaten verfügbar.</p>
<table class="table">
<tr><th>News</th><td><a href="/feed.php?v=rss2&amp;type=news">RSS2</a></td><td><a href="/feed.php?v=atom&amp;type=news">Atom</a></td></tr>
<tr><th>Veränderungen</th><td><a href="/feed.php?v=rss2&amp;type=logs">RSS2</a></td><td><a href="/feed.php?v=atom&amp;type=logs">Atom</a></td></tr>
<tr><th>SQL-Backups</th><td></td><td><a href="https://github.com/georf/Feuerwehrsport-Statistik-Daten/commits/master.atom">Atom</a></td></tr>
<tr><th>Ergebnisse-Backups</th><td></td><td><a href="https://github.com/georf/Feuerwehrsport-Statistik-Ergebnisse/commits/master.atom">Atom</a></td></tr>
<tr><th>Veränderungen der Programmdateien</th><td></td><td><a href="https://github.com/georf/Feuerwehrsport-Statistik/commits/master.atom">Atom</a></td></tr>
</table>
