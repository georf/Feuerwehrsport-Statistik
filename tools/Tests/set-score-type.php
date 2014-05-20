<?php

class SetScoreTypeTest extends ApiTestCase {
  protected function params() {
    $competition = $this->validRow('competitions');
    $score_type  = $this->validRow('score_types');
    return array(
      'get' => array(
        'type' => 'set-score-type'
      ),
      'post' => array(
        'competitionId' => $competition['id'],
        'scoreTypeId'  => $score_type['id'],
      ),
      'session' => array(
        'loggedin' => true,
      )
    );
  }

  public function testSuccess() {
    $this->success($this->api($this->params()));
  }

  public function testFailedBadScore() {
    $params = $this->params();
    $params['post']['competitionId'] = '';
    $this->failed($this->api($params));
  }

  public function testFailedBadTeam() {
    $params = $this->params();
    unset($params['post']['scoreTypeId']);
    $this->failed($this->api($params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'score-type'));
  }
}