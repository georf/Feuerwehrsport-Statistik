<?php

class Link
{
    public static function competition($id, $text = 'Info', $title = 'Details zu diesem Wettkampf anzeigen') {
        global $config;
        return '<a href="'.$config['url'].'page/competition-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($text).'</a>';
    }


    public static function place($id, $name = false, $title = 'Details zu diesem Wettkampfort anzeigen') {
        global $config;
        if ($name === false) {
            $place = FSS::tableRow('places', $id);
            $name = $place['name'];
        }
        return '<a href="'.$config['url'].'page/place-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($name).'</a>';
    }


    public static function event($id, $name = false, $title = 'Details zu diesem Wettkampftyp anzeigen') {
        global $config;
        if ($name === false) {
            $event = FSS::tableRow('events', $id);
            $name = $event['name'];
        }
        return '<a href="'.$config['url'].'page/event-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($name).'</a>';
    }

    public static function team($id, $name = false, $title = 'Details zu diesem Team anzeigen') {
        global $config;
        if ($name === false) {
            $team = FSS::tableRow('teams', $id);
            $name = $team['name'];
        }
        return '<a href="'.$config['url'].'page/team-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($name).'</a>';
    }


    public static function person($id, $text = 'Details', $name = false, $firstname = false, $title = false) {
        global $config;
        if ($name === false || $firstname === false) {
            $person = FSS::tableRow('persons', $id);
            $name = $person['name'];
            $firstname = $person['firstname'];
        }
        if ($title === false) {
            $title = 'Details zu '.$firstname.' '.$name.' anzeigen';
        }

        if ($text === 'sub') {
            $text = substr($firstname,0,1).'. '.$name;
        } elseif ($text === 'full') {
            $text = $firstname.' '.$name;
        }

        return '<a href="'.$config['url'].'page/person-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($text).'</a>';
    }

    public static function subPerson($id, $name = false, $firstname = false, $title = false) {
        return self::person($id, 'sub', $name, $firstname, $title);
    }
    public static function fullPerson($id, $name = false, $firstname = false, $title = false) {
        return self::person($id, 'full', $name, $firstname, $title);
    }

    public static function news($id, $text, $title) {
        return self::page_a('news-'.$id, $text, $title);
    }

    public static function singlediscipline($year, $title = false) {
        if (!$title) $title = 'Einzelergebnisse für das Jahr '.$year;
        return self::page_a('singlediscipline-'.$year, $year, $title);
    }

    public static function date($id, $name = false) {
        if (!$name) $name = 'Details';
        return self::page_a('date-'.$id, $name);
    }

    public static function page_a($page, $name, $title = false) {
        global $config;
        return self::a($config['url'].'page/'.$page.'.html', $name, $title);
    }

    public static function a($url, $name, $title = false) {
        $html_title = (!$title)? '' : ' title="'.htmlspecialchars($title).'"';
        return '<a href="'.htmlspecialchars($url).'"'.$title.'>'.htmlspecialchars($name).'</a>';
    }
}
