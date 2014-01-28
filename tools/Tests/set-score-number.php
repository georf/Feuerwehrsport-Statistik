<?php

class SetScoreNumberTest extends ApiTestCase {
  protected function params() {
    $score = $this->validRow('scores');
    return array(
      'score_id'    => $score['id'],
      'team_number' => '1',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'score-number', $this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['score_id'] = '';
    $this->failed($this->apiPost('set', 'score-number', $params));
  }

  public function testFailedBadTeamNumber() {
    $params = $this->params();
    $params['team_number'] = '';
    $this->failed($this->apiPost('set', 'score-number', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'score-number'));
  }
}