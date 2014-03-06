<?php

class SetScoreNumberTest extends ApiTestCase {
  protected function params() {
    $score = $this->validRow('scores');
    return array(
      'scoreId'    => $score['id'],
      'teamNumber' => '1',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'score-number', $this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['scoreId'] = '';
    $this->failed($this->apiPost('set', 'score-number', $params));
  }

  public function testFailedBadTeamNumber() {
    $params = $this->params();
    $params['teamNumber'] = '';
    $this->failed($this->apiPost('set', 'score-number', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'score-number'));
  }
}