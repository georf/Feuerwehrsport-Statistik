<?php

class GetPersonTest extends ApiTestCase {
  public function testSuccess() {
    $person = $this->validRow('persons');
    $this->success($this->apiPost('get', 'person', array(
      'person_id' => $person['id'],
    )), array('person'));
  }

  public function testFaild() {
    $this->failed($this->apiPost('get', 'person', array(
      'person_id' => "-1",
    )), array('person'));
    $this->failed($this->apiGet('get', 'person'), array('person'));
  } 
}