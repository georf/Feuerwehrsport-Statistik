<?php

class AddEventTest extends ApiTestCase {
  protected function params() {
    return array(
      'name'   => 'test',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('add', 'event', $this->params()));
  }

  public function testFailedBadName() {
    $params = $this->params();
    $params['name'] = '';
    $this->failed($this->apiPost('add', 'event', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('add', 'event'));
  }
}