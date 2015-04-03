<ul>
    <li><a href="/?page=administration&amp;admin=logout">Logout</a></li>
    <li><a href="/?page=administration&amp;admin=import">Import</a></li>
</ul>

<ul>
  <li><h4>Fehler</h4>
    <ul>
      <li><?php echo Link::admin_page_a('errors', 'Fehler bearbeiten'); ?></li>
      <li><?php echo Link::admin_page_a('logs', 'Logs anzeigen'); ?></li>
      <li><?php echo Link::admin_page_a('users', 'anzeigen'); ?></li>
    </ul>
  </li>
  <li><h4>Team</h4>
    <ul>
      <li><?php echo Link::admin_page_a('team_logo_remove_unused', 'Ungenutzte Logos löschen'); ?></li>
      <li><?php echo Link::admin_page_a('team_explode', 'Team auseinanderziehen'); ?></li>
    </ul>
  </li>
  <li><h4>Ergebnis-Dateien</h4>
    <ul>
      <li><?php echo Link::admin_page_a('file_remove', 'löschen'); ?></li>
    </ul>
  </li>
  <li><h4>Neuigkeiten</h4>
    <ul>
      <li><?php echo Link::admin_page_a('news', 'bearbeiten'); ?></li>
    </ul>
  </li>
  <li><h4>D-Cup</h4>
    <ul>
      <li><?php echo Link::admin_page_a('dcup_team', 'D-Cup Teamwertung'); ?></li>
      <li><?php echo Link::admin_page_a('dcup_single', 'D-Cup Einzelwertung'); ?></li>
    </ul>
  </li>
  <li><h4>Personen</h4>
    <ul>
      <li><?php echo Link::admin_page_a('find_persons_without_scores', 'Personen ohne Zuordnung'); ?></li>
    </ul>
  </li>
  <li><h4>Cache</h4>
    <ul>
      <li><?php echo Link::admin_page_a('clean', 'Löschen'); ?></li>
    </ul>
  </li>
</ul>