<?php

class SetScoreWkTest extends ApiTestCase {
  protected function params() {
    $person = $this->validRow('persons');
    $score  = $this->validRow('scores_fs');
    return array(
      'scoreId'    => $score['id'],
      'discipline' => 'fs',
      'person1'    => $person['id'],
      'person2'    => $person['id'],
      'person3'    => $person['id'],
      'person4'    => $person['id'],
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'score-wk', $this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['scoreId'] = '';
    $this->failed($this->apiPost('set', 'score-wk', $params));
  }

  public function testFailedBadDiscipline() {
    $params = $this->params();
    $params['discipline'] = '';
    $this->failed($this->apiPost('set', 'score-wk', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'score-wk'));
  }
}