<?php

class AddScoresTest extends ApiTestCase {
  protected function options() {
    $competition = $this->validRow('competitions');
    return array(
      'get' => array(
        'type' => 'add-scores'
      ),
      'post' => array(
        'discipline' => 'hl',
        'sex' => 'male',
        'competition_id' => $competition['id'],
        'scores' => array(),
      ),
      'session' => array(
        'loggedin' => true,
      )
    );
  }

  public function testSuccess() {
    $this->success($this->api($this->options()));
  }

  public function testFailedNoLogin() {
    $options = $this->options();
    $options['session'] = array();
    $this->failed($this->api($options));
  }

  public function testFailedBadDiscipline() {
    $options = $this->options();
    $options['post']['discipline'] = 'jk';
    $this->failed($this->api($options));
  }

  public function testFailedBadSex() {
    $options = $this->options();
    $options['post']['sex'] = 'other';
    $this->failed($this->api($options));
  }

  public function testFailedBadCompetition() {
    $options = $this->options();
    $options['post']['competition_id'] = '99999999999999';
    $this->failed($this->api($options));
  }

  public function testFailedBadScores() {
    $options = $this->options();
    $options['post']['scores'] = '';
    $this->failed($this->api($options));
  }

  public function testFailed() {
    $options = $this->options();
    $options['post'] = array();
    $this->failed($this->api($options));
  }
}