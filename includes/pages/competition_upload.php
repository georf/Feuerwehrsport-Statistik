<?php
echo Title::h1('PDF - Upload');

Check2::page()->isTrue(Login::check());
Check2::page()->isTrue(isset($_FILES));
$competition = Check2::page()->post('id')->isIn('competitions', 'row');
$calculation = CalculationCompetition::build($competition);
$id = $competition['id'];

if (!is_dir($config['file-path'].''.$id)) {
  mkdir($config['file-path'].''.$id, 0755, true);
}

$disciplines = $calculation->disciplines();

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
  while (is_file($config['file-path'].''.$id.'/'.$name)) {
    $n++;
    $name = $n.$name;
  }

  if (strlen($name) > 250) continue;

  $keys = array();
  foreach ($disciplines as $discipline) {
    if (isset($_POST[$discipline['sexKey'].'_'.$i])) {
      $keys[] = $discipline['sexKey'];
    }
  }

  $insert = array(
    'competition_id' => $id,
    'name' => $name,
    'keys' => implode(',', $keys)
  );

  $db->insertRow('result_files', $insert);

  Log::insert('add-file', $insert);
  move_uploaded_file($tmp_name, $config['file-path'].''.$id.'/'.$name);
  echo '<p>Datei '.htmlspecialchars($name).' wurde gespeichert.</p>';
}

echo '<p><a href="/page/competition-'.$id.'.html">Zurück zum Wettkampf</a></p>';


