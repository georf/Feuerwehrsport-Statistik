<?php

try {
    require_once(__DIR__.'/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

include(__DIR__.'/styling/header.php');

try {
    if (isset($_GET['page'])) {
        $_page = $_GET['page'];
    } else {
        $_page = 'home';
    }

    $path = 'pages/';
    $pageFound = false;

    $vz = opendir($path);
    while ($file = readdir($vz)) {
        if (is_file($path.$file) && $file == $_page.'.php') {
            include(__DIR__.'/'.$path.$file);
            $pageFound = true;
            break;
        }
    }
    closedir($vz);

    if (!$pageFound) throw new PageNotFound();

    $path = 'js/pages/';

    $vz = opendir($path);
    while ($file = readdir($vz)) {
        if (is_file($path.$file) && $file == $_page.'.js') {
            echo '<script type="text/javascript" src="'.$path.$file.'"></script>';
            break;
        }
    }
    closedir($vz);
} catch (PageNotFound $e) {
    $e->sendHeader();
    echo $e->getMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

include(__DIR__.'/styling/footer.php');
