<?php

class GetScoreInformationTest extends ApiTestCase {
  public function testZkSuccess() {
    $score = $this->validRow("scores");
    $this->success($this->apiPost('get', 'score-information', array(
      'scoreId'   => $score['id'],
      'discipline' => 'zk',
    )), array('score', 'scores'));
  }

  public function testLaSuccess() {
    $score = $this->validRow("scores_la");
    $this->success($this->apiPost('get', 'score-information', array(
      'scoreId'   => $score['id'],
      'discipline' => 'la',
    )), array('score', 'scores'));
  }

  public function testFsSuccess() {
    $score = $this->validRow("scores_fs");
    $this->success($this->apiPost('get', 'score-information', array(
      'scoreId'   => $score['id'],
      'discipline' => 'fs',
    )), array('score', 'scores'));
  }

  public function testGsSuccess() {
    $score = $this->validRow("scores_gs");
    $this->success($this->apiPost('get', 'score-information', array(
      'scoreId'   => $score['id'],
      'discipline' => 'gs',
    )), array('score', 'scores'));
  }

  public function testFaild() {
    $this->failed($this->apiPost('get', 'score-information', array(
      'scoreId'   => 'foo',
      'discipline' => 'bar',
    )), array('score', 'scores'));

    $this->failed($this->apiPost('get', 'score-information', array(
      'scoreId'   => 'foo',
      'discipline' => 'zk',
    )), array('score', 'scores'));

    $this->failed($this->apiGet('get', 'score-information'), array('score', 'scores'));
  } 
}