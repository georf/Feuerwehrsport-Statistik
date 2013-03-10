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

    public static function insert($type, $content) {
        global $db;

        if (is_array($content)) {
            $content = json_encode($content);
        }

        $db->insertRow('logs', array(
            'user_id' => Login::getId(),
            'type' => $type,
            'content' => $content
            ));
    }


    public static function getById($id) {
        global $db;

        $row = FSS::tableRow('logs', $id);
        return self::getByRow($row);
    }

    public static function getByRow($row) {
        return new self($row['id'], $row['user_id'], $row['type'], $row['content'], $row['inserted']);
    }

    public function __construct($id, $userId, $type, $content, $inserted) {
        $this->id = $id;
        $this->userId = $userId;
        $this->type = $type;
        $this->raw = json_decode($content, true);
        $this->content = json_decode($content, true);
        $this->inserted = strtotime($inserted);


        switch ($this->type) {

            case 'add-link':
                $this->description = "Link hinzugefügt\n";

                if ($this->raw['for'] == 'competition') {
                    $this->description .= 'für Wettkampf';
                    $this->content = Link::competition($this->raw['for_id'], 'Wettkampf');
                } elseif ($this->raw['for'] == 'team') {
                    $this->description .= 'für Mannschaft';
                    $this->content = Link::team($this->raw['for_id']);
                }

                $this->content .= '<br/>Neu: <a href="'.htmlspecialchars($this->raw['url']).'">'.htmlspecialchars($this->raw['name']).'</a>';
            break;


            case 'add-file':
                $this->description = 'Datei hinzugefügt';

                $competition = $this->raw['competition_id'];

                $this->content = 'Wettkampf: '.
                    Link::event($competition['event_id'], $competition['event']).', '.
                    Link::place($competition['place_id'], $competition['place']).', '.
                    gDate($competition['date']).' - '.
                    Link::competition($competition['id']).'<br/>'.
                    $this->raw['name'].': '.$this->raw['content'];
            break;


            case 'add-team':
                $this->description = 'Team hinzugefügt';
                $this->content = Link::team($this->raw['id'], $this->raw['name']);
            break;


            case 'set-score-team-number':
                $this->description = 'Wertungszeit zugeordnet';

                $person = $this->raw['person'];
                $score = $this->raw['score'];
                $team = $this->raw['team'];
                $competition = FSS::competition($score['competition_id']);

                $this->content = Link::fullPerson($person['id'], $person['name'], $person['firstname']);

                if ($team) $this->content .= ' - '.Link::team($team['id'], $team['name']);

                $this->content .= '<br/>Wettkampf: '.
                    Link::event($competition['event_id'], $competition['event']).', '.
                    Link::place($competition['place_id'], $competition['place']).', '.
                    gDate($competition['date']).' - '.
                    Link::competition($competition['id']).'<br/>'.
                    $score['discipline'].': '.
                    FSS::time($score['time']).'<br/>'.
                    'Geändert zu: '.FSS::teamNumberLong($score['team_number']);
            break;


            case 'set-score-team':
                $this->description = 'Wertungszeit zugeordnet';

                $person = $this->raw['person'];
                $score = $this->raw['score'];
                $team = $this->raw['team'];
                $competition = FSS::competition($score['competition_id']);

                $this->content = Link::fullPerson($person['id'], $person['name'], $person['firstname']);

                $this->content .= '<br/>Wettkampf: '.
                    Link::event($competition['event_id'], $competition['event']).', '.
                    Link::place($competition['place_id'], $competition['place']).', '.
                    gDate($competition['date']).' - '.
                    Link::competition($competition['id']).'<br/>'.
                    $score['discipline'].': '.
                    FSS::time($score['time']).'<br/>'.
                    'Geändert zu: ';
                if ($team) $this->content .= Link::team($team['id'], $team['name'].' - '.FSS::teamNumberLong($score['team_number']));
                else $this->content .= 'Kein Team - '.FSS::teamNumberLong($score['team_number']);
            break;


            case 'set-score-type':
                $this->description = 'Mannschaftswertung geändert';

                $competition = FSS::competition($this->raw['competition']['id']);
                $this->content =
                    Link::event($this->raw['competition']['event_id']).', '.
                    Link::place($this->raw['competition']['place_id']).', '.
                    gDate($this->raw['competition']['date']).' - '.
                    Link::competition($this->raw['competition']['id'], 'Details zum Wettkampf').'<br/>'.
                    'zu: ';
                if ($this->raw['competition']['score_type_id']) {
                    $score = FSS::tableRow('score_types', $this->raw['competition']['score_type_id']);
                    $this->content .= $score['persons'].'/'.$score['run'].'/'.$score['score'];
                } else {
                    $this->content .= 'Keine';
                }
            break;


            case 'set-score-wk':
                $this->description = "Wettkämpfer geändert\n".
                    FSS::dis2name($this->raw['key']);

                $competition = $this->raw['competition'];
                $score = $this->raw['score'];
                $this->content =
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
                if (count($persons)) $this->content .= implode(', ', $persons);
                else $this->content .= 'Keine Wettkämpfer';
            break;
        }
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
