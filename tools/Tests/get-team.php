<?php

class GetTeamTest extends ApiTestCase {
  public function testSuccess() {
    $team = $this->validRow('teams');
    $this->success($this->apiPost('get', 'team', array(
      'teamId' => $team['id'],
    )), array('team'));
  }

  public function testFaild() {
    $this->failed($this->apiPost('get', 'team', array(
      'teamId' => "-1",
    )), array('team'));
    $this->failed($this->apiGet('get', 'team'), array('team'));
  } 
}