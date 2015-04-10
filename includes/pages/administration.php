<?php

if (Check2::boolean()->post('fs_username')->present() && Check2::boolean()->post('fs_password')->present()) {
  if ($_POST['fs_username'] == $config['admin']['username'] && $_POST['fs_password'] == $config['admin']['password']) {
    $_SESSION['loggedin'] = $_SERVER['REMOTE_ADDR'];

    new ChartLoader();
    $myCache = new pCache();
    $myCache->removeOlderThan(86400*3);
    header('Location: /page/administration.html');
    die();
  }
  if ($_POST['fs_username'] == $config['subadmin']['username'] && $_POST['fs_password'] == $config['subadmin']['password']) {
    $_SESSION['subadmin_loggedin'] = $_SERVER['REMOTE_ADDR'];
    header('Location: /page/administration.html');
    die();
  }
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] != $_SERVER['REMOTE_ADDR']) {
  unset($_SESSION['loggedin']);
}
if (isset($_SESSION['subadmin_loggedin']) && $_SESSION['subadmin_loggedin'] != $_SERVER['REMOTE_ADDR']) {
  unset($_SESSION['subadmin_loggedin']);
}


if (!Check2::boolean()->isSubAdmin()) {
  ?>
  <h1>Administraton - Login</h1>
  <form method="post">
    <table>
      <tr><td><label for="fs_username">Username:</label></td><td><input type="text" name="fs_username" id="fs_username"/></td></tr>
      <tr><td><label for="fs_password">Passwort:</label></td><td><input type="password" name="fs_password" id="fs_password"/></td></tr>
      <tr><td></td><td><button type="submit">Anmelden</button></td></tr>
    </table>
  </form>
<?php
} else {
  echo '<link rel="stylesheet" type="text/css" href="/css/administration.css"/>';

  $_admin = (isset($_GET['admin'])) ? $_GET['admin'] : 'overview';
  $path = __DIR__.'/administration/';
  include Check2::except()->variable($_admin)->isInPath($path);
  $footerTags[] = Javascript::scriptTag('js/administration/', $_admin);
}
