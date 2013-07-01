<?php


class Cache {
    private static function getHash($id = false) {
        $hash = '';

        if (isset($_GET) && is_array($_GET)) {
            $get = $_GET;
            ksort($get);
            foreach ($get as $key => $value) {
                $hash .= $key.$value;
            }
        }

        if ($id !== false) {
            $hash .= $id;
        }

        return $hash;
    }


    public static function get($id = false)
    {
        global $db;

        $hash = self::getHash($id);
        $file = basename($_SERVER['SCRIPT_FILENAME']);

        $result = $db->getFirstRow("
            SELECT `data`
            FROM `cache`
            WHERE `file` = '".$db->escape($file)."'
            AND `hash` = '".$db->escape($hash)."'
            LIMIT 1;
        ");

        if (isset($result['data'])) {
            return unserialize($result['data']);
        }
        return false;
    }


    public static function getId()
    {
        return basename($_SERVER['SCRIPT_FILENAME']).self::getHash();
    }


    public static function put($data, $id = false)
    {
        global $db;
        $hash = self::getHash($id);
        $file = basename($_SERVER['SCRIPT_FILENAME']);


        $db->query("
            REPLACE INTO `cache`
            SET
              `file` = '".$db->escape($file)."',
              `hash` = '".$db->escape($hash)."',
              `data` = '".$db->escape(serialize($data))."'
        ");
    }


    public static function clean()
    {
        global $db, $config;

        $db->query("TRUNCATE TABLE `cache`");
        TempDB::clean();
        TempDB::generate('x_team_numbers');

        $vz = opendir($config['cache']);
        while ($file = readdir($vz)) {
            if (preg_match($config['cache-file'], $file) && is_file($config['cache'].$file)) {
                unlink ($file);
            }
        }

        $vz = opendir('page');
        while ($file = readdir($vz)) {
            if (is_file('page/'.$file) && preg_match('|\.html$|', $file)) {
                unlink ('page/'.$file);
            }
        }
    }
        

    public static function generateFile($content) {
        global $config;
        
        $name = $_SERVER['SCRIPT_URL'];

        if (preg_match('|\.php$|', $name)) return;

        file_put_contents($config['base'].preg_replace('|^/|', '', $name), $content);
    }
}
