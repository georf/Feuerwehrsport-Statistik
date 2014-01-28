<?php

class SetTeamLocationTest extends ApiTestCase {
  protected function params() {
    $team = $this->validRow('teams');
    return array(
      'team_id' => $team['id'],
      'lat'     => '11',
      'lon'     => '11',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'team-location', $this->params()));
  }

  public function testFailedBadTeam() {
    $params = $this->params();
    $params['team_id'] = '';
    $this->failed($this->apiPost('set', 'team-location', $params));
  }

  public function testFailedBadLat() {
    $params = $this->params();
    $params['lat'] = '';
    $this->failed($this->apiPost('set', 'team-location', $params));
  }

  public function testFailedBadLon() {
    $params = $this->params();
    $params['lon'] = '';
    $this->failed($this->apiPost('set', 'team-location', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'team-location'));
  }
}