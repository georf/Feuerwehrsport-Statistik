<?php
if (!Check::post('type', 'reason')) throw new Exception('bad input');

$db->insertRow('errors', array(
    'user_id' => Login::getId(),
    'content' => serialize($_POST)
));

mail($config['error-mail'], 'Fehler auf Statistik-Seite', json_encode($_POST, 2));
$output['success'] = true;
