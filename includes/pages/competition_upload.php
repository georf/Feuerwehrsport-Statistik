<?php
Title::set('PDF - Upload');

if (!Login::check() || !Check::isIn($_POST['id'], 'competitions') || !isset($_FILES)) throw new PageNotFound();

if (!is_dir($config['file-path'].''.$_POST['id'])) {
    mkdir($config['file-path'].''.$_POST['id'], 0755, true);
}

$typeCols = array('hl','hbm','hbw','gs','law','lam','fsw','fsm');

for ($i = 0; $i < 100; $i++) {
    if (!isset($_FILES['result_'.$i]['error']) || $_FILES['result_'.$i]['error'] != UPLOAD_ERR_OK) continue;

    $tmp_name = $_FILES['result_'.$i]['tmp_name'];
    $name = preg_replace('|[^a-zA-Z0-9_\-.]|', '_', basename($_FILES['result_'.$i]['name']));

    if (!in_array($_FILES['result_'.$i]['type'], $config['allowed-upload-types'])) {
        echo '<p class="error">Die Datei »'.htmlspecialchars($_FILES['result_'.$i]['name']).'« wurde nicht gespeichert. Sie weißt einen nicht erlaubten Typ auf: »'.htmlspecialchars($_FILES['result_'.$i]['type']).'«</p>';
        continue;
    }
    if (strlen($name) < 5 || substr(strtolower($name), -4) !== '.pdf') {
        echo '<p class="error">Die Datei »'.htmlspecialchars($_FILES['result_'.$i]['name']).'« wurde nicht gespeichert. Sie weißt einen nicht erlaubten Typ auf: »'.htmlspecialchars(substr(strtolower($name), -3)).'«</p>';
        continue;
    }



    $n = 0;
    while (is_file($config['file-path'].''.$_POST['id'].'/'.$name)) {
        $n++;
        $name = $n.$name;
    }

    if (strlen($name) > 250) {
        continue;
    }

    $content = array();
    foreach ($typeCols as $col) {
        if (isset($_POST[$col.'_'.$i])) {
            $content[] = $col;
        }
    }

    $insert = array(
        'competition_id' => $_POST['id'],
        'name' => $name,
        'content' => implode(',', $content)
    );

    $db->insertRow('file_uploads', $insert);


    Log::insert('add-file', $insert);

    move_uploaded_file($tmp_name, $config['file-path'].''.$_POST['id'].'/'.$name);

    echo '<p>Datei '.htmlspecialchars($name).' wurde gespeichert.</p>';

}

echo '<p><a href="/page/competition-'.$_POST['id'].'.html">Zurück zum Wettkampf</a></p>';


