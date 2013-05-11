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
        global $db;

        $db->query("TRUNCATE TABLE `cache`");
        TempDB::clean();
    }
}
