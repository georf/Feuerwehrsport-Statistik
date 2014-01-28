<?php

$type   = Check2::except()->post('type')->present();
$reason = Check2::except()->post('reason')->present();

$db->insertRow('errors', array(
  'user_id' => Login::getId(),
  'content' => serialize($_POST)
));

mail($config['error-mail'], 'Fehler auf Statistik-Seite', json_encode($_POST, 2));
$output['success'] = true;
