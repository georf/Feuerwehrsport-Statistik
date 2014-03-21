<?php

class SetPlaceLocationTest extends ApiTestCase {
  protected function params() {
    $place = $this->validRow('places');
    return array(
      'placeId' => $place['id'],
      'lat'    => '11',
      'lon'    => '11',
    );
  }

  public function testSuccess() {
    $this->success($this->apiPost('set', 'place-location', $this->params()));
  }

  public function testFailedBadPlace() {
    $params = $this->params();
    $params['placeId'] = '';
    $this->failed($this->apiPost('set', 'place-location', $params));
  }

  public function testFailedBadLat() {
    $params = $this->params();
    $params['lat'] = '';
    $this->failed($this->apiPost('set', 'place-location', $params));
  }

  public function testFailedBadLon() {
    $params = $this->params();
    $params['lon'] = '';
    $this->failed($this->apiPost('set', 'place-location', $params));
  }

  public function testFailed() {
    $this->failed($this->apiGet('set', 'place-location'));
  }
}