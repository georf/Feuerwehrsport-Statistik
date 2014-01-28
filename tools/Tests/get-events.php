<?php

class GetEventsTest extends ApiTestCase {
  public function testSuccess() {
    $this->success($this->apiGet('get', 'events'), array('events'));
  } 
}