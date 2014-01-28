<?php

class AddErrorTest extends ApiTestCase {
  protected function params() {
    return array(
      'type'   => 'test',
      'reason' => 'test',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('add', 'error', $this->params()));
  }

  public function testFailedBadType() {
    $params = $this->params();
    $params['type'] = '';
    $this->failed($this->apiPost('add', 'error', $params));
  }

  public function testFailedBadReason() {
    $params = $this->params();
    $params['reason'] = '';
    $this->failed($this->apiPost('add', 'error', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('add', 'error'));
  }
}