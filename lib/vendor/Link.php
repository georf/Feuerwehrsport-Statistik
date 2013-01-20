<?php

class Link
{
    public static function competition($id, $text = 'ⓘ', $title = 'Details zu diesem Wettkampf anzeigen') {
        return '<a href="?page=competition&amp;id='.$id.'" title="'.htmlspecialchars($title).'">'.htmlspecialchars($text).'</a>';
    }


    public static function place($id, $name = false, $title = 'Details zu diesem Wettkampfort anzeigen') {
        if ($name === false) {
            $place = FSS::tableRow('places', $id);
            $name = $place['name'];
        }
        return '<a href="?page=place&amp;id='.$id.'" title="'.htmlspecialchars($title).'">'.htmlspecialchars($name).'</a>';
    }


    public static function event($id, $name = false, $title = 'Details zu diesem Wettkampftyp anzeigen') {
        if ($name === false) {
            $event = FSS::tableRow('events', $id);
            $name = $event['name'];
        }
        return '<a href="?page=event&amp;id='.$id.'" title="'.htmlspecialchars($title).'">'.htmlspecialchars($name).'</a>';
    }

    public static function team($id, $name = false, $title = 'Details zu diesem Team anzeigen') {
        if ($name === false) {
            $team = FSS::tableRow('teams', $id);
            $name = $team['name'];
        }
        return '<a href="?page=team&amp;id='.$id.'" title="'.htmlspecialchars($title).'">'.htmlspecialchars($name).'</a>';
    }


    public static function person($id, $text = 'Details', $name = false, $firstname = false, $title = false) {
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

        return '<a href="?page=person&amp;id='.$id.'" title="'.htmlspecialchars($title).'">'.htmlspecialchars($text).'</a>';
    }

    public static function subPerson($id, $name = false, $firstname = false, $title = false) {
        return self::person($id, 'sub', $name, $firstname, $title);
    }
    public static function fullPerson($id, $name = false, $firstname = false, $title = false) {
        return self::person($id, 'full', $name, $firstname, $title);
    }

}
