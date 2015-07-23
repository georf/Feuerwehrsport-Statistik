<?php

class Log
{
    public $description = '';
    public $content = '';
    public $raw = '';
    public $id = 0;
    public $userId = 0;
    public $type = '';
    public $inserted = 0;

    public static function sendMail($subject, $content) {
      global $config;
      mail($config['error-mail'], $subject, $content."\n\n".$config['url'].'page/administration.html');
    }


    public static function insertWithAlert($type, $content, $cleanCache = true) {
      self::insert($type, $content, $cleanCache, true);
    }

    public static function insert($type, $content, $cleanCache = true, $alert = false) {
        global $db;

        if (is_array($content)) {
            $content = json_encode($content);
        }

        $db->insertRow('logs', array(
            'user_id' => Login::getId(),
            'type' => $type,
            'content' => $content
        ), $cleanCache);
        if ($alert && Check2::boolean()->isSubAdmin() && !Check2::boolean()->isAdmin()) {
          self::sendMail('Sub-Admin-Log auf Statistik-Seite ('.$type.')', $content);
        }
    }


    public static function getById($id) {
        global $db;

        $row = FSS::tableRow('logs', $id);
        return self::getByRow($row);
    }

    public static function getByRow($row) {
        return new self($row['id'], $row['user_id'], $row['type'], $row['content'], $row['inserted']);
    }

    public static function groupByType($logs) {
      $group = array();
      foreach ($logs as $log) {
        if (!isset($group[$log->type])) {
          $group[$log->type] = new LogGroup($log);
        } else {
          $group[$log->type]->logs[] = $log;
        }
      }
      return $group;
    }

    public function __construct($id, $userId, $type, $content, $inserted) {
        $this->id = $id;
        $this->userId = $userId;
        $this->type = $type;
        $this->raw = json_decode($content, true);
        $this->content = json_decode($content, true);
        $this->inserted = strtotime($inserted);

        $types = self::types();
        if (isset($types[$this->type])) {
          $this->typeArray = $types[$this->type];
          $this->typeArray[0]($this);
          $this->translations = $this->typeArray[1];
        } else {
          $this->translations = array();
        }

    }

    public static $types = false;

    public static function types() {
      if (!is_array(self::$types)) {
        self::$types = array(
          'add-event' => array(
            function ($log) {
              $log->description = "Wettkampftyp hinzugefügt";
              $log->content = Link::event($log->raw['id'], $log->raw['name']);
            },
            array('ein neue Wettkampftyp hinzugefügt', '%d neue Wettkampftypen hinzugefügt')
          ),
          'add-logo' => array(
            function ($log) {
              $log->description = "Logo hinzugefügt";
              $log->content = Link::team($log->raw['team_id']);
            },
            array('ein neues Team-Logo hinzugefügt', '%d neue Team-Logos hinzugefügt')
          ),
          'add-news' => array(
            function ($log) {
              $log->description = "Neuigkeit hinzugefügt";
              $log->content = Link::news($log->raw['id'], $log->raw['title'], $log->raw['title']);
            },
            array()
          ),
          'set-news' => array(
            function ($log) {
              $log->description = "Neuigkeit geändert";
              $log->content = Link::news($log->raw['id'], $log->raw['title'], $log->raw['title']);
            },
            array()
          ),
          'add-link' => array(
            function ($log) {
              $log->description = "Link hinzugefügt\n";

              if ($log->raw['for'] == 'competition') {
                $log->description .= 'für Wettkampf';
                $log->content = Link::competition($log->raw['for_id'], 'Wettkampf');
              } elseif ($log->raw['for'] == 'team') {
                $log->description .= 'für Mannschaft';
                $log->content = Link::team($log->raw['for_id']);
              } elseif ($log->raw['for'] == 'date') {
                $log->description .= 'für Termin';
                $log->content = Link::date($log->raw['for_id']);
              }

              $log->content .= '<br/>Neu: <a href="'.htmlspecialchars($log->raw['url']).'">'.htmlspecialchars($log->raw['name']).'</a>';
            },
            array('ein neuer Link hinzugefügt', '%d neue Links hinzugefügt')
          ),
          'add-file' => array(
            function ($log) {
              $log->description = 'Datei hinzugefügt';

              $competitionId = isset($log->raw['competition_id']) ? $log->raw['competition_id'] : $log->raw['competition']['id'];
              $competition = FSS::competition($competitionId);

              $keys = isset($log->raw['content']) ? $log->raw['content'] : $log->raw['keys'];

              $log->content = 'Wettkampf: '.
                Link::event($competition['event_id'], $competition['event']).', '.
                Link::place($competition['place_id'], $competition['place']).', '.
                gDate($competition['date']).' - '.
                Link::competition($competition['id']).'<br/>'.
                $log->raw['name'].': '.$keys;
            },
            array('eine neue Ergebnis-Datei hochgeladen', '%d neue Ergebnis-Dateien hochgeladen')
          ),
          'add-date' => array(
            function ($log) {
              $log->description = 'Termin hinzugefügt';
              $log->content = Link::date($log->raw['id'], $log->raw['name']);
            },
            array()
          ),
          'add-team' => array(
            function ($log) {
              $log->description = 'Team hinzugefügt';
              $log->content = Link::team($log->raw['id'], $log->raw['name']);
              },
            array('eine neue Mannschaft ergänzt', '%d neue Mannschaften ergänzt')
          ),
          'add-place' => array(
            function ($log) {
              $log->description = 'Ort hinzugefügt';
              $log->content = Link::place($log->raw['id'], $log->raw['name']);
            },
            array('eine neuer Wettkampfort hinzugefügt', '%d neue Wettkampforte hinzugefügt')
          ),
          'add-competition' => array(
            function ($log) {
              $log->description = 'Wettkampf hinzugefügt';
              $competition = FSS::competition($log->raw['id']);
              $infos = array(gDate($log->raw['date']));
              if ($competition) {
                  $infos[] = $competition["place"];
                  $infos[] = $competition["event"];
              }
              if (!empty($log->raw['name'])) $infos[] = $log->raw['name'];
              $log->content = Link::competition($log->raw['id'], implode(" - ", $infos));
            },
            array()
          ),
          'set-place-location' => array(
            function ($log) {
              $log->description = 'Kartenposition angepasst';
              if (isset($log->raw['place'])) {
                  $log->content = Link::place($log->raw['place']['id'], $log->raw['place']['name']);
              } elseif (isset($log->raw['team'])) {
                  $log->content = Link::team($log->raw['team']['id'], $log->raw['team']['name']);
              }
            },
            array('Kartenposition für Wettkampfort angepasst', 'Kartenpositionen für %d Wettkampforte angepasst')
          ),
          'set-competition-name' => array(
            function ($log) {
              $log->description = 'Name für Wettkampf hinzugefügt';
              $competition = FSS::competition($log->raw['id']);
              $log->content = 
                  Link::competition($log->raw['id'], gDate($competition['date'])." - ".$competition['place']).
                  "<br/>".htmlspecialchars($log->raw['name']);
            },
            array('eine neuer Wettkampfname vergeben', '%d neue Wettkampfnamen vergeben')
          ),
          'add-hint' => array(
            function ($log) {
              $log->description = 'Hinweis hinzugefügt';
              $competition = FSS::competition($log->raw['competition_id']);
              $log->content = 
                  Link::competition($log->raw['id'], gDate($competition['date'])." - ".$competition['place']).
                  "<br/><pre>".htmlspecialchars($log->raw['hint'])."</pre>";
            },
            array('eine neuer Hinweis zu einem Wettkampf ergänzt', '%d neue Hinweise zu Wettkämpfen ergänzt')
          ),
          'delete-hint' => array(
            function ($log) {
              $log->description = "Hinweis gelöscht";
              $competition = FSS::competition($log->raw['competition_id']);
              $log->content = 
                  Link::competition($log->raw['id'], gDate($competition['date'])." - ".$competition['place']).
                  "<br/><pre>".htmlspecialchars($log->raw['hint'])."</pre>";
            },
            array()
          ),
          'set-score-team-number' => array(
            function ($log) {
              $log->description = 'Wertungszeit zugeordnet';

              $person = $log->raw['person'];
              $score = $log->raw['score'];
              $team = $log->raw['team'];
              $competition = FSS::competition($score['competition_id']);

              $log->content = Link::fullPerson($person['id'], $person['name'], $person['firstname']);

              if ($team) $log->content .= ' - '.Link::team($team['id'], $team['name']);

              $log->content .= '<br/>Wettkampf: '.
                  Link::event($competition['event_id'], $competition['event']).', '.
                  Link::place($competition['place_id'], $competition['place']).', '.
                  gDate($competition['date']).' - '.
                  Link::competition($competition['id']).'<br/>'.
                  $score['discipline'].': '.
                  FSS::time($score['time']).'<br/>'.
                  'Geändert zu: '.FSS::teamNumberLong($score['team_number']);
            },
            array()
          ),
          'set-score-team' => array(
            function ($log) {
              $log->description = 'Wertungszeit zugeordnet';

              $person = $log->raw['person'];
              $score = $log->raw['score'];
              $team = $log->raw['team'];
              $competition = FSS::competition($score['competition_id']);

              $log->content = Link::fullPerson($person['id'], $person['name'], $person['firstname']);

              $log->content .= '<br/>Wettkampf: '.
                  Link::event($competition['event_id'], $competition['event']).', '.
                  Link::place($competition['place_id'], $competition['place']).', '.
                  gDate($competition['date']).' - '.
                  Link::competition($competition['id']).'<br/>'.
                  $score['discipline'].': '.
                  FSS::time($score['time']).'<br/>'.
                  'Geändert zu: ';
              if ($team) $log->content .= Link::team($team['id'], $team['name'].' - '.FSS::teamNumberLong($score['team_number']));
              else $log->content .= 'Kein Team - '.FSS::teamNumberLong($score['team_number']);
            },
            array()
          ),
          'set-score-type' => array(
            function ($log) {
              $log->description = 'Mannschaftswertung geändert';

              if (is_array($log->raw['competition'])) {
                  $competition = FSS::competition($log->raw['competition']['id']);
                  $log->content =
                      Link::event($log->raw['competition']['event_id']).', '.
                      Link::place($log->raw['competition']['place_id']).', '.
                      gDate($log->raw['competition']['date']).' - '.
                      Link::competition($log->raw['competition']['id'], 'Details zum Wettkampf').'<br/>'.
                      'zu: ';
                  if ($log->raw['competition']['score_type_id']) {
                      $score = FSS::tableRow('score_types', $log->raw['competition']['score_type_id']);
                      $log->content .= $score['persons'].'/'.$score['run'].'/'.$score['score'];
                  } else {
                      $log->content .= 'Keine';
                  }
              }
            },
            array()
          ),
          'set-team-state' => array(
            function ($log) {
              $log->description = 'Team einem Bundesland zugeordnet';
              $log->content = Link::team($log->raw['team']['id'], $log->raw['team']['name']);
            },
            array('einer Mannschaft einem Bundesland zugeordnet', '%d Mannschaften einem Bundesland zugeordnet')
          ),
          'set-score-wk' => array(
            function ($log) {
              $log->description = "Wettkämpfer geändert\n".
                  FSS::dis2name($log->raw['key']);

              $competition = $log->raw['competition'];
              $score = $log->raw['score'];
              $log->content =
                  Link::event($competition['event_id'], $competition['event']).', '.
                  Link::place($competition['place_id'], $competition['place']).', '.
                  gDate($competition['date']).' - '.
                  Link::competition($competition['id'], 'Details zum Wettkampf').'<br/>'.
                  FSS::time($score['time']).' ('.Link::team($score['team_id']).')<br/>zu: ';

              $persons = array();
              for ($i = 0; $i < 8; $i++)  {
                  if (isset($score['person_'.$i]) && Check::isIn($score['person_'.$i], 'persons')) {
                      $persons[] = Link::subPerson($score['person_'.$i]);
                  }
              }
              if (count($persons)) $log->content .= implode(', ', $persons);
              else $log->content .= 'Keine Wettkämpfer';
            },
            array('Personen einem Wettkampflauf zugeordnet', 'Personen zu %d Wettkampfläufen zugeordnet')
          ),
          'add-person' => array(
            function ($log) {
              $log->description = 'Person hinzugefügt';
              $log->content = Link::fullPerson($log->raw['id'], $log->raw['name'], $log->raw['firstname']);
            },
            array('eine neue Person hinzugefügt', '%d neue Personen hinzugefügt')
          ),
          'set-team-location' => array(
            function ($log) {
              $log->description = "Kartenposition geändert";

              $team = $log->raw['team'];
              $log->content = Link::team($team['id'], $team['name']);
            },
            array('Kartenposition für Mannschaft angepasst', 'Kartenpositionen für %d Mannschaften angepasst')
          )
        );
      }
      return self::$types;
    }

    public function datetime() {
        return date('d.m.Y H:i', $this->inserted);
    }

    public function time() {
        return $this->inserted;
    }

    public function description() {
        return $this->description;
    }

    public function descriptionHtml() {
        return nl2br($this->description);
    }

    public function content() {
        if (is_string($this->content)) return $this->content;
        return $this->type.'<pre>'.print_r($this->content, true).'</pre>';
    }
}


class LogGroup {
  public $logs = array();

  public function __construct($log) {
    $this->logs[] = $log;
  }

  public function showContent() {
    return count($this->logs[0]->translations) == 2;
  }

  public function count() {
    return count($this->logs);
  }

  public function __toString() {
    if ($this->showContent()) {
      $count = $this->count();
      if ($count == 1) {
        return $this->logs[0]->typeArray[1][0];
      } else {
        return sprintf($this->logs[0]->typeArray[1][1], $count);
      }
    }
    return "";
  }
}