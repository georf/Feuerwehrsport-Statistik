<?php

class Login
{
    static public function check()
    {

        return (self::getId() !== false);
    }

    static public function in($name, $email, $ip, $useragent)
    {
        global $db;

        if (self::check()) {
            return true;
        }

        $hash = rand(-999,999);

        $result = $db->insertRow('users', array(
            'name' => $name,
            'email' => $email,
            'ip' => $ip,
            'useragent' => $useragent,
            'hash' => $hash
        ), false);

        if ($result) {
            $_SESSION['login'] = $hash;
            return true;
        }
        return false;
    }

    static public function getId()
    {
        global $db;

        if (isset ($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }

        if (isset($_SESSION['login'])) {
            $row = $db->getFirstRow("
                SELECT `id`
                FROM `users`
                WHERE `hash` = '".$db->escape($_SESSION['login'])."'
                AND `ip` = '".$db->escape($ip)."'
                LIMIT 1;
            ");

            if ($row) return $row['id'];
        }
        return false;
    }

    public static function getMailLink($id) {
        global $db;

        $user = $db->getFirstRow("
                SELECT `email`
                FROM `users`
                WHERE `id` = '".$db->escape($id)."'
                LIMIT 1;
            ", 'email');

        if (empty($user)) {
            return '';
        }

        return '<a href="mailto:'.$user.'">➦</a>';
    }

    public static function getNameLink($id) {
        global $db;

        $user = $db->getFirstRow("
                SELECT `name`
                FROM `users`
                WHERE `id` = '".$db->escape($id)."'
                LIMIT 1;
            ", 'name');

        if (empty($user)) {
            $user = $id;
        }

        return '<a href="?page=administration&amp;admin=user&amp;id='.$id.'">'.$user.'</a>';
    }
}
