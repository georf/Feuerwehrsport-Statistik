<?php

class SetScoreTeamTest extends ApiTestCase {
  protected function params() {
    $score = $this->validRow('scores');
    $team  = $this->validRow('teams');
    return array(
      'score_id' => $score['id'],
      'team_id'  => $team['id'],
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'score-team', $this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['score_id'] = '';
    $this->failed($this->apiPost('set', 'score-team', $params));
  }

  public function testFailedBadTeam() {
    $params = $this->params();
    unset($params['team_id']);
    $this->failed($this->apiPost('set', 'score-team', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'score-team'));
  }
}