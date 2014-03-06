<?php

class Check2Test extends ApiTestCase {
  public function testExcept() {
    global $_POST, $_GET, $_SERVER;
    $_GET    = array('foo' => 'bar');
    $_POST   = array('foo' => 'bar');
    $_SERVER = array('foo' => 'bar');

    $this->throwsException(function () {
      Check2::except()->get('foobar');
    });
    $this->compare(Check2::except()->get('foo')->getVal(), 'bar');

    $this->throwsException(function () {
      Check2::except()->post('foobar');
    });
    $this->compare(Check2::except()->post('foo')->getVal(), 'bar');

    $this->throwsException(function () {
      Check2::except()->server('foobar');
    });
    $this->compare(Check2::except()->server('foo')->getVal(), 'bar');
  }

  public function testIsIn() {
    global $_POST;
    $competition = $this->validRow("competitions");
    $_POST = array(
      'foo'             => 'bar',
      'competitionId'  => $competition['id'],
      'competitionId2' => 0,
    );

    // array
    $this->isTrue(Check2::boolean()->post('foo')->isIn(array('bar')));
    $this->isFalse(Check2::boolean()->post('foo')->isIn(array('bar2')));

    // database
    $this->isTrue(Check2::boolean()->post('competitionId')->isIn('competitions'));
    $this->isFalse(Check2::boolean()->post('competitionId2')->isIn('competitions'));
    $this->isNull(Check2::boolean()->post('competitionId2')->isIn('competitions', true));
    $this->isArray(Check2::boolean()->post('competitionId')->isIn('competitions', 'row'));
  }

  public function testIsSex() {
    $this->compare(Check2::value()->variable('male')->isSex(), 'male');
    $this->compare(Check2::value()->variable('female')->isSex(), 'female');
    $this->compare(Check2::value()->variable('other')->isSex(), false);
    $this->compare(Check2::value(null)->variable('other')->isSex(), null);
  }

  public function testIsAdmin() {
    global $_SESSION;

    $_SESSION = array('loggedin' => true);
    $this->isTrue(Check2::except()->isAdmin());

    $_SESSION = array();
    $this->throwsException(function () {
      Check2::except()->isAdmin();
    });
  }

  public function testIsDate() {
    $this->isTrue(Check2::boolean()->variable('2012-20-20')->isDate());
    $this->isFalse(Check2::boolean()->variable('202-20-20')->isDate());
  }

  public function testPresent() {
    $this->isTrue(Check2::boolean()->variable('foo')->present());
    $this->isTrue(Check2::boolean()->variable("\t")->present());
    $this->isFalse(Check2::boolean()->variable('')->present());
  }

  public function testIsDiscipline() {
    $this->isTrue(Check2::boolean()->variable('hl')->isDiscipline());
    $this->isFalse(Check2::boolean()->variable('as')->isDiscipline());
    $this->isFalse(Check2::boolean()->variable('')->isDiscipline());
  }

  public function testIsInPath() {
    $path = __DIR__.'/';
    $myself = preg_replace('|\.php$|', '', basename(__FILE__));
    $this->compare(Check2::boolean()->variable($myself)->isInPath($path), __FILE__);
    $this->isFalse(Check2::boolean()->variable('foobar')->isInPath($path));
  }

  public function testIsArray() {
    $this->isTrue(Check2::boolean()->variable(array())->isArray());
    $this->isFalse(Check2::boolean()->variable('')->isArray());
  }

  public function testIsNumber() {
    $this->isTrue(Check2::boolean()->variable('1234')->isNumber());
    $this->isFalse(Check2::boolean()->variable('asdf')->isNumber());
  }
}