<?php

class SetScoreTeamTest extends ApiTestCase {
  protected function params() {
    $score = $this->validRow('scores');
    $team  = $this->validRow('teams');
    return array(
      'scoreId' => $score['id'],
      'teamId'  => $team['id'],
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'score-team', $this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['scoreId'] = '';
    $this->failed($this->apiPost('set', 'score-team', $params));
  }

  public function testFailedBadTeam() {
    $params = $this->params();
    unset($params['teamId']);
    $this->failed($this->apiPost('set', 'score-team', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'score-team'));
  }
}