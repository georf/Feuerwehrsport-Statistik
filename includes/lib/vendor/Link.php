<?php

class Link
{
    public static function competition($id, $text = 'Info', $title = 'Details zu diesem Wettkampf anzeigen') {
        global $config;
        return '<a href="'.$config['url'].'page/competition-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($text).'</a>';
    }

    public static function competitions($name = "Wettkämpfe", $title = "Wettkämpfe anzeigen") {
        return self::page_a('competitions', $name, $title);
    }

    public static function databaseLinksFor($table, $id) {
        global $db;        
        $links = array();
        foreach ($db->getRows("
          SELECT `url`, `name`
          FROM `links`
          WHERE `for_id` = '".$id."'
          AND `for` = '".$table."'
        ") as $link) {
            $links[] = Link::a($link['url'], $link['name']);
        }
        return $links;
    }

    public static function place($id, $name = false, $title = 'Details zu diesem Wettkampfort anzeigen') {
        global $config;
        if ($name === false) {
            $place = FSS::tableRow('places', $id);
            $name = $place['name'];
        }
        return '<a href="'.$config['url'].'page/place-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($name).'</a>';
    }

    public static function places($name = "Wettkampforte", $title = "Wettkampforte anzeigen") {
        return self::page_a('places', $name, $title);
    }

    public static function event($id, $name = false, $title = 'Details zu diesem Wettkampftyp anzeigen') {
        global $config;
        if ($name === false) {
            $event = FSS::tableRow('events', $id);
            $name = $event['name'];
        }
        return '<a href="'.$config['url'].'page/event-'.$id.'.html" title="'.htmlspecialchars($title).'">'.htmlspecialchars($name).'</a>';
    }

    public static function events($name = "Wettkampftypen", $title = 'Wettkampftypen anzeigen') {
        return self::page_a('events', $name, $title);
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

    public static function bestScoresOfYear($year, $name = false, $title = false) {
        if (!$name) $name = $year;
        if (!$title) $title = 'Bestzeiten des Jahres '.$year;
        return self::page_a('best-scores-of-year-'.$year, $name, $title);
    }

    public static function bestPerformanceOfYear($year, $name = false, $title = false) {
        if (!$name) $name = $year;
        if (!$title) $title = 'Bestleistungen des Jahres '.$year;
        return self::page_a('best-performance-of-year-'.$year, $name, $title);
    }

    public static function singlediscipline($year, $title = false) {
        return self::bestPerformanceOfYear($year, false, $title);
    }

    public static function year($year, $text = false, $title = false) {
        if (!$text) $text = $year;
        if (!$title) $title = "Überblick über das Jahr ".$year;
        return self::page_a('year-'.$year, $text, $title);
    }

    public static function years($text = false) {
        if (!$text) $text = 'Jahre';
        return self::page_a('years', $text);
    }

    public static function date($id, $name = false) {
        if (!$name) $name = 'Details';
        return self::page_a('date-'.$id, $name);
    }

    public static function page_a($page, $name, $title = false) {
        global $config;
        return self::a($config['url'].'page/'.$page.'.html', $name, $title);
    }

    public static function dcup_single($year, $discipline, $sex, $under, $display = false, $title = false) {
        global $config;

        $under = $under ? 'u' : '';
        if ($display == false) $display = 'Details';
        return self::page_a('dcup_single-'.$year.'-'.$discipline.$sex.$under, $display, $title);
    }

    public static function dcup($year, $display = false, $title = false) {
        global $config;

        if ($display == false) $display = 'D-Cup-Gesamtwertung '.$year;
        return self::page_a('dcup-'.$year, $display, $title);
    }

    public static function admin_page_a($page, $name) {
        global $config;
        return self::a($config['url'].'?page=administration&admin='.$page, $name);        
    }

    public static function a($url, $name, $title = false) {
        $html_title = (!$title)? '' : ' title="'.htmlspecialchars($title).'"';
        return '<a href="'.htmlspecialchars($url).'"'.$html_title.'>'.htmlspecialchars($name).'</a>';
    }

  public static function linksForTeam($id) {
    return self::linksFor('team', $id);
  }

  public static function linksFor($for, $id) {
    global $db;
    $links = array();
    foreach ($db->getRows("
      SELECT `url`, `name`
      FROM `links`
      WHERE `for_id` = '".$id."'
      AND `for` = 'team'
    ") as $link) {
      $links[] = self::a($link['url'], $link['name']);
    }
    return $links;
  }

  public static function actionIcon($icon, $id, $title, $data) {
    $output = '<span class="action-icon" id="'.$id.'" title="'.htmlspecialchars($title).'"';

    foreach ($data as $key => $value) {
      $output .= ' data-'.$key.'="'.$value.'"';
    }
    $output .= '><img src="/styling/images/'.$icon.'.png" alt=""/></span>';
    return $output;
  }
}
