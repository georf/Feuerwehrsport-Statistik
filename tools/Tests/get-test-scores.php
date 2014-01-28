<?php

class GetTestScoresTest extends ApiTestCase {
  protected function options() {
    return array(
      'get' => array(
        'type' => 'get-test-scores'
      ),
      'post' => array(
        'discipline' => 'hb',
        'sex' => 'male',
        'raw_scores' => 'Limbach;Georg;FF Warin;19,22;18,99',
        'seperator' => ';',
        'headlines' => 'name,firstname,team,time,time',
      ),
      'session' => array(
        'loggedin' => true,
      )
    );
  }

  public function testSuccess() {
    $this->success($this->api($this->options()), array('teams', 'scores'));
  }

  public function testFailedNoLogin() {
    $options = $this->options();
    $options['session'] = array();
    $this->failed($this->api($options), array('teams', 'scores'));
  }

  public function testFailedBadSex() {
    $options = $this->options();
    $options['post']['sex'] = 'other';
    $this->failed($this->api($options), array('teams', 'scores'));
  }

  public function testFailedBadRawScores() {
    $options = $this->options();
    $options['post']['raw_scores'] = '';
    $this->failed($this->api($options), array('teams', 'scores'));
  }

  public function testFailedBadSeperator() {
    $options = $this->options();
    $options['post']['seperator'] = '';
    $this->failed($this->api($options), array('teams', 'scores'));
  }

  public function testFailedBadHeadline() {
    $options = $this->options();
    $options['post']['headlines'] = '';
    $this->failed($this->api($options), array('teams', 'scores'));
  }

  public function testFailed() {
    $options = $this->options();
    $options['post'] = array();
    $this->failed($this->api($options), array('teams', 'scores'));
  }
}