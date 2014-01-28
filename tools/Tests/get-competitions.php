<?php

class GetCompetitionsTest extends ApiTestCase {
  public function testSuccess() {
    $this->success($this->apiGet('get', 'competitions'), array('competitions'));
  } 
}