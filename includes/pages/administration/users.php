<h1>Benutzer</h1>
<table class="table">
  <thead>
    <tr>
      <th>Name</th>
      <th>E-Mail</th>
      <th>IP</th>
      <th>Useragent</th>
    </tr>
  </thead>
  <tbody>
<?php
$users = $db->getRows("
  SELECT *
  FROM `users`
  ORDER BY id DESC
  LIMIT 100
");
foreach ($users as $user) {
  echo
    '<tr><td>',
      $user['name'],
    '</td><td>',
      $user['email'],
    '</td><td>',
      $user['ip'],
    '</td><td>',
      $user['useragent'],
    '</td><td>',
      $user['inserted'],
    '</td></tr>';
}
?>
  </tbody>
</table>
