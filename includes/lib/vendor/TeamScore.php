<?php

class TeamScore {
  private $scores;
  private $competitionScore;

  public static function build($scores, $score) {
    $instance = new self();
    $instance->scores = $scores;
    $instance->competitionScore = $score;
    return $instance;
  }

  public function unsorted() {
    $teamScores = array();
    foreach ($this->scores as $score) {
      if ($score['team_number'] < 0) continue;
      if (!$score['team_id']) continue;

      $uniqTeam = $score['team_id'].$score['team_number'];
      if (!isset($teamScores[$uniqTeam])) {
        $teamScores[$uniqTeam] = array(
          'name' => $score['team'],
          'short' => $score['shortteam'],
          'id' => $score['team_id'],
          'number' => $score['team_number'],
          'scores' => array(),
        );
      }

      $teamScores[$uniqTeam]['scores'][] = $score;
    }

    // sort every persons in teams
    foreach ($teamScores as $uniqTeam => $team) {
      $time = 0;

      usort($team['scores'], function($a, $b) {
        if ($a['time'] == $b['time']) return 0;
        elseif ($a['time'] > $b['time']) return 1;
        else return -1;
      });

      if (count($team['scores']) < $this->competitionScore) {
        $teamScores[$uniqTeam]['time'] = FSS::INVALID;
        continue;
      }

      for ($i = 0; $i < $this->competitionScore; $i++) {
        if (FSS::isInvalid($team['scores'][$i]['time'])) {
          $teamScores[$uniqTeam]['time'] = FSS::INVALID;
          continue 2;
        }
        $time += $team['scores'][$i]['time'];
      }
      $teamScores[$uniqTeam]['time'] = $time;
    }

    return $teamScores;
  }

  public function sorted() {
    $scores = $this->unsorted();

    // Sortiere Teams nach Zeit
    uasort($scores, function ($a, $b) {
      if ($a['time'] == $b['time']) return 0;
      elseif ($a['time'] > $b['time']) return 1;
      else return -1;
    });

    return $scores;
  }
}