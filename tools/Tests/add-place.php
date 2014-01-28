<?php

class AddPlaceTest extends ApiTestCase {
  protected function params() {
    return array(
      'name'   => 'test',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('add', 'place', $this->params()));
  }

  public function testFailedBadName() {
    $params = $this->params();
    $params['name'] = '';
    $this->failed($this->apiPost('add', 'place', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('add', 'place'));
  }
}