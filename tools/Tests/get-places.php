<?php

class GetPlacesTest extends ApiTestCase {
  public function testSuccess() {
    $this->success($this->apiGet('get', 'places'), array('places'));
  } 
}