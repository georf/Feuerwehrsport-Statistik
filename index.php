<?php

try {
    require_once(__DIR__.'/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

// cache header for edit title
ob_start();
include(__DIR__.'/styling/header.php');
$header = ob_get_contents();
ob_end_clean();

try {
    if (isset($_GET['page'])) {
        $_page = $_GET['page'];
    } else {
        $_page = 'home';
    }

    $path = 'pages/';
    $pageFound = false;
    $no_cache = array('administration', 'competition_upload', 'year', 'discipline','place');

    $vz = opendir($path);
    while ($file = readdir($vz)) {
        if (is_file($path.$file) && $file == $_page.'.php') {

            $content = '';

            // check for admin page
            if (isset($_SESSION['loggedin']) && is_file($path.'administration/pages/'.$file)) {

                ob_start();
                include(__DIR__.'/'.$path.'administration/pages/'.$file);
                $header .= ob_get_contents();
                ob_end_clean();
            }

            if (in_array($_page, $no_cache)) {

                ob_start();
                include(__DIR__.'/'.$path.$file);

                $content = ob_get_contents();
                ob_end_clean();
            } else {
                $cache = Cache::get();
                if ($cache) {
                    $content = $cache['content'];
                    Title::set($cache['title']);
                } else {
                    ob_start();

                    include(__DIR__.'/'.$path.$file);

                    $content = ob_get_contents();
                    ob_end_clean();

                    $cache = array(
                        'content' => $content,
                        'title' => Title::get(),
                    );
                    Cache::put($cache);
                }
            }

            echo Title::replace($header, 'Feuerwehrsport-Statistik');
            echo $content;

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
    ob_end_clean();
    $e->sendHeader();

    echo Title::replace($header, '404 - Seite nicht gefunden');

    echo $e->getMessage();
} catch (Exception $e) {

    echo Title::replace($header, '500 - Interner Fehler');
    echo $e->getMessage();
}

include(__DIR__.'/styling/footer.php');
