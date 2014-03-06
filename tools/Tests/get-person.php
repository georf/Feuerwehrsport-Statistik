<?php

class GetPersonTest extends ApiTestCase {
  public function testSuccess() {
    $person = $this->validRow('persons');
    $this->success($this->apiPost('get', 'person', array(
      'personId' => $person['id'],
    )), array('person'));
  }

  public function testFaild() {
    $this->failed($this->apiPost('get', 'person', array(
      'personId' => "-1",
    )), array('person'));
    $this->failed($this->apiGet('get', 'person'), array('person'));
  } 
}