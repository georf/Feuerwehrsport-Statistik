<?php

class SetScoreWkTest extends ApiTestCase {
  protected function params() {
    $person = $this->validRow('persons');
    $score  = $this->validRow('scores_fs');
    return array(
      'score_id'   => $score['id'],
      'discipline' => 'fs',
      'person_1'   => $person['id'],
      'person_2'   => $person['id'],
      'person_3'   => $person['id'],
      'person_4'   => $person['id'],
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'score-wk', $this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['score_id'] = '';
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