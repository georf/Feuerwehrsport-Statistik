<?php

class GetPersonsTest extends ApiTestCase {
  public function testSuccess() {
    $this->success($this->apiGet('get', 'persons'), array('persons'));
    $this->success($this->apiPost('get', 'persons', array(
      'sex' => 'other',
    )), array('persons'));
  }

  public function testMale() {
    $response = $this->apiPost('get', 'persons', array(
      'sex' => 'male',
    ));
    $this->success($response, array('persons'));    
    foreach ($response['persons'] as $person) {
      $this->compare($person['sex'], 'male');
    }
  }

  public function testFemale() {
    $response = $this->apiPost('get', 'persons', array(
      'sex' => 'female',
    ));
    $this->success($response, array('persons'));    
    foreach ($response['persons'] as $person) {
      $this->compare($person['sex'], 'female');
    }
  }
}