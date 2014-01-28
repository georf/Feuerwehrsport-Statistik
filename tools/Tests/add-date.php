<?php

class AddDateTest extends ApiTestCase {
  protected function params() {
    $place = $this->validRow('places');
    $event = $this->validRow('events');
    return array(
      'date'        => date('Y-m-d'),
      'name'        => 'test',
      'description' => 'test',
      'place_id'    => $place['id'],
      'event_id'    => $event['id'],
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('add', 'date', $this->params()));
  }

  public function testFailedBadDate() {
    $params = $this->params();
    $params['date'] = 'other';
    $this->failed($this->apiPost('add', 'date', $params));
  }

  public function testFailedBadPlace() {
    $params = $this->params();
    unset($params['place_id']);
    $this->failed($this->apiPost('add', 'date', $params));
  }

  public function testFailedBadEvent() {
    $params = $this->params();
    unset($params['event_id']);
    $this->failed($this->apiPost('add', 'date', $params));
  }

  public function testFailedBadName() {
    $params = $this->params();
    $params['name'] = '';
    $this->failed($this->apiPost('add', 'date', $params));
  }

  public function testFailedBadDescription() {
    $params = $this->params();
    $params['description'] = '';
    $this->failed($this->apiPost('add', 'date', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('add', 'date'));
  }
}