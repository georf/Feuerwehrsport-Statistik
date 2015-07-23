<?php

echo Title::set('Feuerwehrsport - verschiedene Angebote');

echo Bootstrap::row()
  ->col('<p class="lead">Die Webseite Feuerwehrsport-Statistik.de stellt einige digitale Werke zum Thema Feuerwehrsport zur Verfügung, die auf dieser Seite aufgelistet sind.</p>', 9)
  ->col(TableOfContents::get()
    ->link('wettkampf-manager', 'Wettkampf-Mangaer')
    ->link('einfuerungs-videos', 'Einführungsvideos')
  , 3);

echo Title::h2('Wettkampf-Manager', 'wettkampf-manager');
echo Bootstrap::row()
  ->col(
    '<p>Der Wettkampf-Manager ist ein Programm zum Auswerten von Wettkämpfen. Unabhängig von Excel können Startlisten, Ergebnislisten und Gesamtwertungen erstellt werden. Die Eingabe der Daten ist über mehrere PCs gleichzeitig möglich. Eine Live-Tabelle kann über WLAN bereitgestellt werden.</p>'
    , 9)
  ->col(Link::page_a('wettkampf-manager', "Weitere Information", false, array("btn", "btn-default")), 3);

echo Title::h2('Einführungsvideos in die Disziplinen', 'einfuerungs-videos');
echo Bootstrap::row()
  ->col(
    '<p>Nachfolgend sind zu den einzelnen Disziplinen Einführungsvideos aufgelistet. Diese erklären neben den Abmaßen und Regeln auch die vereinfachten Abläufe.</p>'.
    '<p>Um gute Leistungen zu erzielen sollte man sich aber an eine Feuerwehrsportmannschaft aus der Umgebung wenden. Diese kann im persönlichen Gespräch meistens schneller auf Probleme eingehen und euch wertvolle Tipps geben.</p>'.
    '<p><a href="https://www.youtube.com/watch?v=BPsXhdzzkJ4&list=PLeV2Fd7RaQ4QzYOjYq0e7qEf4mQE0aVHk" class="btn btn-default">Alle wiedergeben</a></p>'
    , 6)
  ->col('<h4>Gruppenstafette</h4><iframe width="400" height="225" src="https://www.youtube-nocookie.com/embed/6bgLFx7TM6U" frameborder="0" allowfullscreen></iframe>', 6)
  ->col('<h4>Hindernisbahn</h4><iframe width="400" height="225" src="https://www.youtube-nocookie.com/embed/BPsXhdzzkJ4" frameborder="0" allowfullscreen></iframe>', 6)
  ->col('<h4>Löschangriff nass</h4><iframe width="400" height="225" src="https://www.youtube-nocookie.com/embed/lRv5uaX_R_M" frameborder="0" allowfullscreen></iframe>', 6)
  ->col('<h4>4x100-Meter-Feuerwehrstafette</h4><iframe width="400" height="225" src="https://www.youtube-nocookie.com/embed/gOBOix6p2So" frameborder="0" allowfullscreen></iframe>', 6)
  ->col('<h4>Hakenleitersteigen</h4><iframe width="400" height="225" src="https://www.youtube-nocookie.com/embed/1k9u6sngP-c" frameborder="0" allowfullscreen></iframe>', 6);