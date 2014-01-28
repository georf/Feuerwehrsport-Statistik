<?php

class AddTeamTest extends ApiTestCase {
  protected function params() {
    return array(
      'name'  => 'test',
      'short' => 'test',
      'type'  => 'Team',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('add', 'team', $this->params()));
  }

  public function testFailedBadName() {
    $params = $this->params();
    $params['name'] = '';
    $this->failed($this->apiPost('add', 'team', $params));
  }

  public function testFailedBadShort() {
    $params = $this->params();
    $params['short'] = '';
    $this->failed($this->apiPost('add', 'team', $params));
  }

  public function testFailedBadType() {
    $params = $this->params();
    $params['type'] = 'other';
    $this->failed($this->apiPost('add', 'team', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('add', 'team'));
  }
}