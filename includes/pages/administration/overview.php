<ul>
    <li><a href="/?page=administration&amp;admin=logout">Logout</a></li>
    <li><a href="/?page=administration&amp;admin=import">Import</a></li>
    <li><a href="/?page=administration&amp;admin=find_bad_teams">Find same teams</a></li>
    <li><a href="/?page=administration&amp;admin=find_bad_persons">Find bad persons</a> - <a href="/?page=administration&amp;admin=find_bad_persons2">Find same persons</a></li>
</ul>

<ul>
  <li><h4>Fehler</h4>
    <ul>
      <li><?php echo Link::admin_page_a('errors', 'Fehler bearbeiten'); ?></li>
      <li><?php echo Link::admin_page_a('logs', 'Logs anzeigen'); ?></li>
    </ul>
  </li>
  <li><h4>Team-Logo</h4>
    <ul>
      <li><?php echo Link::admin_page_a('team_logo_add', 'hinzufügen'); ?></li>
      <li><?php echo Link::admin_page_a('team_logo_remove_unused', 'Ungenutzte löschen'); ?></li>
    </ul>
  </li>
  <li><h4>Ergebnis-Dateien</h4>
    <ul>
      <li><?php echo Link::admin_page_a('file_remove', 'löschen'); ?></li>
    </ul>
  </li>
  <li><h4>Benutzer</h4>
    <ul>
      <li><?php echo Link::admin_page_a('users', 'anzeigen'); ?></li>
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
  <li><h4>Cache</h4>
    <ul>
      <li><?php echo Link::admin_page_a('clean', 'Löschen'); ?></li>
    </ul>
  </li>
</ul>