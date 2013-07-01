<?php

if (Check::post('fs_username', 'fs_password')) {
    if ($_POST['fs_username'] == $config['admin']['username']
    && $_POST['fs_password'] == $config['admin']['password']) {
        $_SESSION['loggedin'] = $_SERVER['REMOTE_ADDR'];

        new ChartLoader();
        /* Create the cache object */
        $myCache = new pCache();

        /* Remove objects older than tree day */
        $myCache->removeOlderThan(86400*3);
    }
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] != $_SERVER['REMOTE_ADDR']) {
    unset($_SESSION['loggedin']);
}


if (!isset($_SESSION['loggedin'])) {
    ?>
    <h1>Administraton - Login</h1>
    <form method="post">
        <table>
            <tr><td><label for="fs_username">Username:</label></td><td><input type="text" name="fs_username" id="fs_username"/></td></tr>
            <tr><td><label for="fs_password">Passwort:</label></td><td><input type="password" name="fs_password" id="fs_password"/></td></tr>
            <tr><td></td><td><button type="submit">Anmelden</button></td></tr>
        </table>

    </form>

<?php
} else {

    if (isset($_GET['admin'])) {
        $_admin = $_GET['admin'];
    } else {
        $_admin = 'overview';
    }

    $path = __DIR__.'/administration/';

    $vz2 = opendir($path);
    while ($file = readdir($vz2)) {
        if (is_file($path.$file) && $file == $_admin.'.php') {
            include($path.$file);
            break;
        }
    }
    closedir($vz2);
}
