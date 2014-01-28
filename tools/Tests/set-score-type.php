<?php

class SetScoreTypeTest extends ApiTestCase {
  protected function params() {
    $competition = $this->validRow('competitions');
    $score_type  = $this->validRow('score_types');
    return array(
      'competition_id' => $competition['id'],
      'score_type_id'  => $score_type['id'],
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'score-type', $this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['competition_id'] = '';
    $this->failed($this->apiPost('set', 'score-type', $params));
  }

  public function testFailedBadTeam() {
    $params = $this->params();
    unset($params['score_type_id']);
    $this->failed($this->apiPost('set', 'score-type', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'score-type'));
  }
}