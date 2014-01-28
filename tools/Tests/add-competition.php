<?php

class AddCompetitionTest extends ApiTestCase {
  protected function options() {
    $place = $this->validRow('places');
    $event = $this->validRow('events');
    return array(
      'get' => array(
        'type' => 'add-competition'
      ),
      'post' => array(
        'date' => date('Y-m-d'),
        'name' => 'test',
        'place_id' => $place['id'],
        'event_id' => $event['id'],
      ),
      'session' => array(
        'loggedin' => true,
      )
    );
  }

  public function testSuccess() {
    $this->success($this->api($this->options()));
  }

  public function testFailedNoLogin() {
    $options = $this->options();
    $options['session'] = array();
    $this->failed($this->api($options));
  }

  public function testFailedBadDate() {
    $options = $this->options();
    $options['post']['date'] = 'other';
    $this->failed($this->api($options));
  }

  public function testFailedBadPlace() {
    $options = $this->options();
    $options['post']['place_id'] = '';
    $this->failed($this->api($options));
  }

  public function testFailedBadEvent() {
    $options = $this->options();
    $options['post']['event_id'] = '';
    $this->failed($this->api($options));
  }

  public function testFailed() {
    $options = $this->options();
    $options['post'] = array();
    $this->failed($this->api($options));
  }
}