<?php

if (Check::post('team') && Check::isIn($_POST['team'], 'teams') && isset($_FILES['logo'])) {

    $tmp_name = $_FILES['logo']['tmp_name'];
    $name = preg_replace('|[^a-zA-Z0-9_\-.]|', '_', basename($_FILES['logo']['name']));
    $basename = preg_replace('|\.[^.]+$|', '', $name);
    $new_name = $basename.'.png';

    $n = 0;
    while (is_file($config['logo-path'].$name) || is_file($config['logo-path'].$new_name)) {
        $n++;
        $name = $n.$name;
    }


    $db->updateRow('teams', $_POST['team'], array(
        'logo' => $new_name
    ));


    move_uploaded_file($tmp_name, $config['logo-path'].$name);

    if ($name != $new_name) {
        shell_exec('convert '.$config['logo-path'].$name.' '.$config['logo-path'].$new_name);
        unlink($config['logo-path'].$name);
    }

    shell_exec('mogrify -resize 100x100 -background transparent -gravity center -extent 100x100 -format png '.$config['logo-path'].$new_name);

    echo '<p>Datei '.$new_name.' wurde gespeichert.</p>';

}



echo '<form method="post" enctype="multipart/form-data"><input type="file" name="logo"/><br/><select name="team">';

$teams = $db->getRows("
    SELECT *
    FROM `teams`
    WHERE `logo` IS NULL
    ORDER BY `name`
");

foreach ($teams as $team) {
    echo '<option value="'.$team['id'].'">'.$team['name'].'</option>';
}
echo '</select><br/><input type="submit"></form>';
