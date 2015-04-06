<?php

try {
    require_once(__DIR__.'/includes/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
 if ($severity) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
  }
}

new ChartLoader();

try {
    if (!isset($_GET['type'])) throw new Exception('bad chart type');

    $_page = $_GET['type'];


    ob_start();
    $path = 'includes/charts/';
    $vz = opendir($path);
    $foundChart = false;
    while ($file = readdir($vz)) {
        if (is_file($path.$file) && $file == $_page.'.php') {
            include(__DIR__.'/'.$path.$file);
            $foundChart = true;
            break;
        }
    }
    closedir($vz);

    if (!$foundChart) {
        throw new Exception('bad chart type');
    }


    $no_cache = array('overview_best_year');

    $content = ob_get_contents();
    if (!in_array($_page, $no_cache)) Cache::generateFile($content);
    ob_end_clean();
    die($content);
} catch (Exception $e) {
    $message = wordwrap($e->getMessage());
    $lines = explode("\n", $message);
    $maxChars = 0;
    foreach ($lines as $line) {
        $maxChars = max($maxChars, strlen($line));
    }

    /* Create the pChart object */
    $myPicture = new pImage($maxChars * 7 + 15, count($lines) * 17 + 7);

    /* Set the default font */
    $myPicture->setFontProperties(array(
        "FontName" => PCHARTDIR."fonts/UbuntuMono-R.ttf",
        "FontSize" => 7,
        "R" => 0,
        "G" => 0,
        "B" => 0
    ));

    /* Write text */
    $myPicture->drawText(10, count($lines) * 15 +1, $message, array(
        "DrawBox" => true,
        "BoxRounded" => true,
        "BoxR" => 255,
        "BoxG" => 34,
        "BoxB" => 22,
        "Angle" => 0,
        "FontSize" => 10
    ));

    /* Render the picture */
    $myPicture->stroke();
}
