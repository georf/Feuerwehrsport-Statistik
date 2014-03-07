<?php

class GetDateTest extends ApiTestCase {
  public function testSuccess() {
    $date = $this->validRow('dates');
    $this->success($this->apiPost('get', 'date', array(
      'dateId' => $date['id'],
    )), array('date'));
  }

  public function testFaild() {
    $this->failed($this->apiPost('get', 'date', array(
      'dateId' => "-1",
    )), array('date'));
    $this->failed($this->apiGet('get', 'date'), array('date'));
  } 
}