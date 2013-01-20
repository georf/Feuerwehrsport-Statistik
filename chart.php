<?php

try {
    require_once(__DIR__.'/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

if (isset($_GET['type'])) {
	$_page = $_GET['type'];
} else {
  exit();
}

new ChartLoader();


$path = 'charts/';
$vz = opendir($path);
while ($file = readdir($vz)) {
	if (is_file($path.$file) && $file == $_page.'.php') {
		include(__DIR__.'/'.$path.$file);
		break;
	}
}
closedir($vz);
