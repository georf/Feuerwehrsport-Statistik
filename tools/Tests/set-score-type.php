<?php

class SetScoreTypeTest extends ApiTestCase {
  protected function params() {
    $competition = $this->validRow('competitions');
    $score_type  = $this->validRow('score_types');
    return array(
      'competitionId' => $competition['id'],
      'scoreTypeId'  => $score_type['id'],
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'score-type', $this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['competitionId'] = '';
    $this->failed($this->apiPost('set', 'score-type', $params));
  }

  public function testFailedBadTeam() {
    $params = $this->params();
    unset($params['scoreTypeId']);
    $this->failed($this->apiPost('set', 'score-type', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'score-type'));
  }
}