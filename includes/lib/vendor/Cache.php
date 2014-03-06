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

        if (isset($config['no-clean']) && $config['no-clean']) return;

        $db->query("TRUNCATE TABLE `cache`");
        TempDB::clean();
        TempDB::generate('x_team_numbers');

        shell_exec('find '.$config['base'].'chart/ -type f | egrep "\.png$" | xargs rm');
        shell_exec('find '.$config['base'].'page/ -type f | egrep "\.html$" | xargs rm');
    }


    public static function generateFile($content) {
        global $config;

        if (isset($config['cache-disabled'])) return;

        $name = $_SERVER['SCRIPT_URL'];

        if (preg_match('|\.php$|', $name)) return;
        if (preg_match('|^/$|', $name)) return;

        file_put_contents($config['base'].preg_replace('|^/|', '', $name), $content);
    }
}
