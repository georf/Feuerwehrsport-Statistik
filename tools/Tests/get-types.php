<?php

class GetTypesTest extends ApiTestCase {
  public function testSuccess() {
    $this->success($this->apiGet('get', 'score-types'), array('types'));
  } 
}