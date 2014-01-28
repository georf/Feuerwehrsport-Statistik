<?php

class AddPersonTest extends ApiTestCase {
  protected function params() {
    return array(
      'name'     => 'test',
      'firstname' => 'test',
      'sex'      => 'male',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('add', 'person', $this->params()));
  }

  public function testFailedBadName() {
    $params = $this->params();
    $params['name'] = '';
    $this->failed($this->apiPost('add', 'person', $params));
  }

  public function testFailedBadFirstname() {
    $params = $this->params();
    $params['firstname'] = '';
    $this->failed($this->apiPost('add', 'person', $params));
  }

  public function testFailedBadSex() {
    $params = $this->params();
    $params['sex'] = 'other';
    $this->failed($this->apiPost('add', 'person', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('add', 'person'));
  }
}