<?php

$type   = Check2::except()->post('type')->present();
$reason = Check2::except()->post('reason')->present();

if (isset($_FILES) && count($_FILES) > 0) {
  foreach ($_FILES as $file) {
    if (!isset($file['error']) || $file['error'] != UPLOAD_ERR_OK) continue;

    $tmp_name = $file['tmp_name'];
    $name = preg_replace('|[^a-zA-Z0-9_\-.]|', '_', basename($file['name']));

    if (!in_array($file['type'], $config['allowed-logo-upload-types'])) {
        throw new Exception('Die Datei »'.$file['name'].'« wurde nicht gespeichert. Sie weißt einen nicht erlaubten Typ auf: »'.$file['type'].'«');
    }

    $i = 1;
    while (is_file($config['error-file-path'].$i.$name)) $i++;
    move_uploaded_file($tmp_name, $config['error-file-path'].$i.$name);

    if (!isset($_POST["attached_files"])) {
      $_POST["attached_files"] = array();
    }
    $_POST["attached_files"][] = $i.$name;
  }
}

$db->insertRow('errors', array(
  'user_id' => Login::getId(),
  'content' => serialize($_POST)
));

mail($config['error-mail'], 'Fehler auf Statistik-Seite', json_encode($_POST, 2));
$output['success'] = true;
