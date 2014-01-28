<?php

class AddLinkTest extends ApiTestCase {
  protected function params() {
    $competition = $this->validRow('competitions');
    return array(
      'name'   => 'test',
      'for' => 'competition',
      'id' => $competition['id'],
      'url' => 'http://example'
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('add', 'link', $this->params()));
  }

  public function testFailedBadName() {
    $params = $this->params();
    $params['name'] = '';
    $this->failed($this->apiPost('add', 'link', $params));
  }

  public function testFailedBadFor() {
    $params = $this->params();
    $params['for'] = 'other';
    $this->failed($this->apiPost('add', 'link', $params));
  }

  public function testFailedBadId() {
    $params = $this->params();
    $params['id'] = '999999999999999999999';
    $this->failed($this->apiPost('add', 'link', $params));
  }

  public function testFailedBadUrl() {
    $params = $this->params();
    $params['url'] = '';
    $this->failed($this->apiPost('add', 'link', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('add', 'link'));
  }
}