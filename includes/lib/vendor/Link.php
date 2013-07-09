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
        global $config;
        return '<a href="'.$config['url'].'page/news-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($text).'</a>';
    }

    public static function singlediscipline($year, $title = false) {
        global $config;
        if (!$title) $title = 'Einzelergebnisse für das Jahr '.$year;
        return '<a href="'.$config['url'].'page/singlediscipline-'.$year.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($year).'</a>';
    }
}
