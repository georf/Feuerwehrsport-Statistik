<?php

class DCupTeam {
    public $id;
    public $number;
    public $name;
    public $competitions = array();

    public function __construct($row) {
        $this->id = $row['team_id'];
        $this->number = $row['team_number'];
        $this->name = $row['team'];

        if ($this->number != 0) $this->name .= " ".($this->number + 1);
    }

    public function addScore($row) {
        if ($row['team_id'] != $this->id) return;
        if ($row['team_number'] != $this->number) return;

        if (!isset($this->competitions[$row['competition_id']])) {
            $this->competitions[$row['competition_id']] = array();
        }

        $this->competitions[$row['competition_id']][] = $row;
    }

    public function getSum($competition_id = false) {
        if ($competition_id === false) {
            $sum = 0;
            foreach ($this->competitions as $key => $value) {
                $sum += $this->getSum($key);
            }
            return $sum;
        }

        $sum = 0;
        if (isset($this->competitions[$competition_id])) {
            foreach ($this->competitions[$competition_id] as $score) {
                $sum += $score['points'];
            }
        }
        return $sum;
    }

    public function getMaxLines() {
        $max = 0;
        foreach ($this->competitions as $competition) {
            $max = max(count($competition), $max);
        }
        return $max;
    }

    public function getScore($competition_id, $line) {
        if (isset($this->competitions[$competition_id][$line])) {
            return $this->competitions[$competition_id][$line];
        } else {
            return array('discipline' => '', 'points' => '');
        }
    }
}
