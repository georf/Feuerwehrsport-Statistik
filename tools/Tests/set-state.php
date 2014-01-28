<?php

class SetStateTest extends ApiTestCase {
  protected function params() {
    $team = $this->validRow('teams');
    return array(
      'id'    => $team['id'],
      'for'   => 'team',
      'state' => 'mv',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'state', $this->params()));
  }

  public function testFailedBadId() {
    $params = $this->params();
    $params['id'] = '';
    $this->failed($this->apiPost('set', 'state', $params));
  }

  public function testFailedBadFor() {
    $params = $this->params();
    $params['for'] = '';
    $this->failed($this->apiPost('set', 'state', $params));
  }

  public function testFailedBadState() {
    $params = $this->params();
    unset($params['state']);
    $this->failed($this->apiPost('set', 'state', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'state'));
  }
}