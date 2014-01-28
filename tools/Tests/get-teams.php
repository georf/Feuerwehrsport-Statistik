<?php

class GetTeamsTest extends ApiTestCase {
  public function testSuccess() {
    $this->success($this->apiGet('get', 'teams'), array('teams'));
  } 
}