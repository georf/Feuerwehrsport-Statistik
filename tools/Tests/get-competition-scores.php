<?php

class GetCompetitionScoresTest extends ApiTestCase {
  public function testSuccess() {
    $competition = $this->validRow("competitions");
    $this->success($this->apiPost('get', 'competition-scores', array(
      'competition_id' => $competition['id'],
    )), array('scores'));
  }

  public function testFaild() {
    $this->failed($this->apiPost('get', 'competition-scores', array(
      'competition_id' => "-1",
    )), array('scores'));
    $this->failed($this->apiGet('get', 'competition-scores'), array('scores'));
  } 
}