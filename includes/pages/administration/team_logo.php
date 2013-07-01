<?php


if (Check::post('team') && Check::isIn($_POST['team'], 'teams') && isset($_FILES['logo'])) {

    $tmp_name = $_FILES['logo']['tmp_name'];
    $name = preg_replace('|[^a-zA-Z0-9_\-.]|', '_', basename($_FILES['logo']['name']));

    $n = 0;
    while (is_file($config['logo-path'].$name)) {
        $n++;
        $name = $n.$name;
    }


    $db->updateRow('teams', $_POST['team'], array(
        'logo' => $name
    ));


    move_uploaded_file($tmp_name, $config['logo-path'].$name);
    
    $full_path = $config['logo-path'].$name;
    
    $imageOutput = new Imagick(); // This will hold the resized image
    $image = new Imagick($full_path); // Open image file
    $image->thumbnailImage(100,100, true);
    $imageOutput->newImage(100, 100, "none"); // Make the container with transparency
    $imageOutput->compositeImage($image, Imagick::COMPOSITE_DEFAULT, ((100 - $image->getImageWidth())/2), ((100 - $image->getImageHeight())/2) ); // Center the resized image inside of the container
    $imageOutput->setImageFormat('png'); // Set the format to maintain transparency
    $imageOutput->writeImage($full_path); // Write it to disk
    $image->clear(); //cleanup -v
    $image->destroy(); 
    $imageOutput->clear();
    $imageOutput->destroy();
  

    echo '<p>Datei '.$name.' wurde gespeichert.</p>';

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
