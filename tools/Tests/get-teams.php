<?php

class GetTeamsTest extends ApiTestCase {
  public function testSuccess() {
    $this->success($this->apiGet('get', 'teams'), array('teams'));
  }
  public function testWithPersonIdSuccess() {
    $this->success($this->apiPost('get', 'teams', array(
      'personId' => 271
    )), array('teams' => function ($teams) {
      return $teams[0]["value"] == 2;
    }));
  }
  public function testWithCompetitionIdSuccess() {
    $this->success($this->apiPost('get', 'teams', array(
      'competitionId' => 273
    )), array('teams' => function ($teams) {
      return $teams[0]["value"] == 23 && count($teams) == 7;
    }));
  }
  public function testWithCompetitionIdAndSexSuccess() {
    $this->success($this->apiPost('get', 'teams', array(
      'competitionId' => 33,
      'sex' => 'female'
    )), array('teams' => function ($teams) {
      return count($teams) == 0;
    }));
  }
}