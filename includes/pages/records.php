<?php

$team2 = FSS::tableRow('teams', 2);
$team11 = FSS::tableRow('teams', 11);
$team15 = FSS::tableRow('teams', 15);
$team61 = FSS::tableRow('teams', 61);

echo Title::set('Rekorde');

echo '<table class="table">';
echo '<tr><td colspan="7"><h3>Weltrekorde - Männer</h3></td></tr>';
echo '<tr><th colspan="2">Wettkampf</th><th>Name</th><th colspan="2">Nationalität</th><th>Zeit</th><th>Aufgestellt</th></tr>';

echo '<tr><td>'.FSS::dis2img('hl').'</td>'.
  '<td>'.FSS::dis2name('hl').'</td>'.
  '<td>Albert Loginov</td>'.
  '<td><img src="/styling/images/flagge_russland.png" alt="Russland"/></td>'.
  '<td>Russland</td>'.
  '<td>12,56 s</td>'.
  '<td>'.Link::competition(54, '8. WM 2012 - Antalya (Türkei)').'</td></tr>';
echo '<tr><td>'.FSS::dis2img('hb').'</td>'.
  '<td>'.FSS::dis2name('hb').'</td>'.
  '<td>Vladimir Sidorenko</td>'.
  '<td><img src="/styling/images/flagge_russland.png" alt="Russland"/></td>'.
  '<td>Russland</td>'.
  '<td>14,77 s</td>'.
  '<td>9. WM 2013 - Jinju (Südkorea)</td></tr>';
echo '<tr><td>'.FSS::dis2img('zk').'</td>'.
  '<td>'.FSS::dis2name('zk').'</td>'.
  '<td>Vladimir Sidorenko</td>'.
  '<td><img src="/styling/images/flagge_russland.png" alt="Russland"/></td>'.
  '<td>Russland</td>'.
  '<td>27,75 s<br/><small>12,63 s/15,12 s</small></td>'.
  '<td>9. WM 2013 - Jinju (Südkorea)</td></tr>';
echo '<tr><td>'.FSS::dis2img('fs').'</td>'.
  '<td>'.FSS::dis2name('fs').'</td>'.
  '<td></td>'.
  '<td><img src="/styling/images/flagge_weissrussland.png" alt="Weißrussland"/></td>'.
  '<td>Weißrussland</td>'.
  '<td>53,52 s</td>'.
  '<td>4. WM 2008 - Sofia (Bulgarien)</td></tr>';
echo '<tr><td>'.FSS::dis2img('la').'</td>'.
  '<td>'.FSS::dis2name('la').'<br/><small>(ISFFR - 20 m C)</small></td>'.
  '<td></td>'.
  '<td><img src="/styling/images/flagge_tschechien.png" alt="Tschechien"/></td>'.
  '<td>Tschechien</td>'.
  '<td>25,14 s</td>'.
  '<td>'.Link::competition(54, '8. WM 2012 - Antalya (Türkei)').'</td></tr>';

echo '<tr><td colspan="7"><h3>Deutsche Rekorde - Männer</h3></td></tr>';
echo '<tr><th colspan="2">Wettkampf</th><th>Name</th><th colspan="2">Mannschaft</th><th>Zeit</th><th>Aufgestellt</th></tr>';

echo '<tr><td>'.FSS::dis2img('hl').'</td>'.
  '<td>'.FSS::dis2name('hl').'</td>'.
  '<td>'.Link::person(184, 'full', 'Gehlert', 'Tom').'</td>'.
  '<td>'.TeamLogo::get(11, $team11['logo']).'</td>'.
  '<td>'.Link::team(11, 'Thüringen-Auswahl').'</td>'.
  '<td>14,27 s</td>'.
  '<td>'.Link::competition(225, '1. D-Cup 2013 - Zeulenroda').'</td></tr>';
echo '<tr><td>'.FSS::dis2img('hb').'</td>'.
  '<td>'.FSS::dis2name('hb').'</td>'.
  '<td>'.Link::person(239, 'full', 'Daßler', 'Adrian').'</td>'.
  '<td>'.TeamLogo::get(15, $team15['logo']).'</td>'.
  '<td>'.Link::team(15, 'TSV Zeulenroda').'</td>'.
  '<td>16,46 s</td>'.
  '<td>'.Link::competition(12, '2. D-Cup 2012 - Zeulenroda').'</td></tr>';
echo '<tr><td>'.FSS::dis2img('zk').'</td>'.
  '<td>'.FSS::dis2name('zk').'</td>'.
  '<td>'.Link::person(239, 'full', 'Daßler', 'Adrian').'</td>'.
  '<td>'.TeamLogo::get(15, $team15['logo']).'</td>'.
  '<td>'.Link::team(15, 'TSV Zeulenroda').'</td>'.
  '<td>31,63 s<br/><small>15,17 s/16,46 s</small></td>'.
  '<td>'.Link::competition(12, '2. D-Cup 2012 - Zeulenroda').'</td></tr>';
echo '<tr><td>'.FSS::dis2img('fs').'</td>'.
  '<td>'.FSS::dis2name('fs').'</td>'.
  '<td><small>'.implode(', ', array(Link::person(227, 'sub'), Link::person(92, 'sub'), Link::person(68, 'sub'), Link::person(179, 'sub'))).'</small></td>'.
  '<td>'.TeamLogo::get(2, $team2['logo']).'</td>'.
  '<td>'.Link::team(2, 'Team Mecklenburg-Vorpommern').'</td>'.
  '<td>59,60 s</td>'.
  '<td>'.Link::competition(232, 'XV. CTIF 2013 - Mulhouse').'</td></tr>';
echo '<tr><td>'.FSS::dis2img('la').'</td>'.
  '<td>'.FSS::dis2name('la').'<br/><small>(ISFFR - 20 m C)</small></td>'.
  '<td><small>'.implode(', ', array(Link::person(246, 'sub'), Link::person(237, 'sub'), Link::person(184, 'sub'), '<br/>'.Link::person(42, 'sub'), Link::person(100, 'sub'), Link::person(79, 'sub'), Link::person(68, 'sub'))).'</small></td>'.
  '<td>'.TeamLogo::get(61, $team61['logo']).'</td>'.
  '<td>'.Link::team(61, 'Team Deutschland').'</td>'.
  '<td>26,56 s</td>'.
  '<td>'.Link::competition(56, '7. WM 2011 - Cottbus').'</td></tr>';

echo '<tr><td colspan="7"><h3>Deutsche Rekorde - Frauen</h3></td></tr>';
echo '<tr><th colspan="2">Wettkampf</th><th>Name</th><th colspan="2">Mannschaft</th><th>Zeit</th><th>Aufgestellt</th></tr>';

echo '<tr><td>'.FSS::dis2img('hb').'</td>'.
  '<td>'.FSS::dis2name('hb').'</td>'.
  '<td>'.Link::person(8, 'full', 'Marek', 'Stephanie').'</td>'.
  '<td>'.TeamLogo::get(2, $team2['logo']).'</td>'.
  '<td>'.Link::team(2, 'Team Mecklenburg-Vorpommern').'</td>'.
  '<td>18,20 s</td>'.
  '<td>'.Link::competition(12, '2. D-Cup 2012 - Zeulenroda').'</td></tr>';
echo '<tr><td>'.FSS::dis2img('fs').'</td>'.
  '<td>'.FSS::dis2name('fs').'</td>'.
  '<td><small>'.implode(', ', array(Link::person(8, 'sub'), Link::person(251, 'sub'), '<br/>'.Link::person(282, 'sub'), Link::person(116, 'sub'))).'</small></td>'.
  '<td>'.TeamLogo::get(2, $team2['logo']).'</td>'.
  '<td>'.Link::team(2, 'Team Mecklenburg-Vorpommern').'</td>'.
  '<td>66,89 s</td>'.
  '<td>'.Link::competition(53, 'DM 2012 - Cottbus').'</td></tr>';
echo '<tr><td>'.FSS::dis2img('gs').'</td>'.
  '<td>'.FSS::dis2name('gs').'</td>'.
  '<td><small>'.implode(', ', array(Link::person(116, 'sub'), Link::person(284, 'sub'), Link::person(282, 'sub'), '<br/>'.Link::person(8, 'sub'), Link::person(35, 'sub'), Link::person(251, 'sub'))).'</small></td>'.
  '<td>'.TeamLogo::get(2, $team2['logo']).'</td>'.
  '<td>'.Link::team(2, 'Team Mecklenburg-Vorpommern').'</td>'.
  '<td>82,42 s</td>'.
  '<td>'.Link::competition(53, 'DM 2012 - Cottbus').'</td></tr>';

echo '</table>';

echo '<p>Stand: 01.11.2013</p>';