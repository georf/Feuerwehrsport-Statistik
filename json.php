<?php

try {
    require_once(__DIR__.'/includes/lib/init.php');
} catch (Exception $e) {
    die($e->getMessage());
}

$output = array('login' => Login::check());

if (isset($_GET['type'])) {

    if ($_GET['type'] === 'login') {
        if (isset($_POST['name'], $_POST['email'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']) && !empty($_POST['name'])) {
            $output['login'] = Login::in($_POST['name'], $_POST['email'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            $output['debug'] = print_r($_SESSION, true);
        } else {
            $output['message'] = 'Bitte geben Sie die geforderten Daten ein.';
        }
    }

    // only logged in

    if (Login::check()) {
        $_type = $_GET['type'];

        if (preg_match('/^((get)|(set)|(add))-(.+)$/', $_type, $result)) {
            $type = $result[1];
            $request = $result[5];

            try {
                if (!in_array($type, array('set', 'get', 'add'))) throw new Exception();

                $path = __DIR__.'/includes/api/'.$type.'/';
                $found = false;
                $vz = opendir($path);
                while ($file = readdir($vz)) {
                    if ($file === $request.'.php') {
                        include ($path.$file);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $output['message'] = 'bad request';
                    $output['success'] = false;
                    $output['debug'] = array(
                        'post' => $_POST,
                        'get' => $_GET
                    );
                }
            } catch (Exception $e) {
                $output['success'] = false;
                $output['message'] = $e->getMessage();
            }
        } else {
            $output['nothing'] = true;
            $output['debug'] = array(
                'post' => $_POST,
                'get' => $_GET
            );
        }
    }
}


echo json_encode($output);
