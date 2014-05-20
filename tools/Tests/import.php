<?php

class ImportTest extends TestCase {
  public function testGetTeamNumber() {
    $this->compare(Import::getTeamNumber('1234'), 1);
    $this->compare(Import::getTeamNumber('foobar 1'), 1);
    $this->compare(Import::getTeamNumber('foobar I'), 1);
    $this->compare(Import::getTeamNumber('foobar 2'), 2);
    $this->compare(Import::getTeamNumber('foobar II'), 2);
    $this->compare(Import::getTeamNumber('foobar 3'), 3);
    $this->compare(Import::getTeamNumber('foobar III'), 3);
    $this->compare(Import::getTeamNumber('foobar 4'), 4);
    $this->compare(Import::getTeamNumber('foobar IV'), 4);
    $this->compare(Import::getTeamNumber('foobar E'), 0);
    $this->compare(Import::getTeamNumber(' foobar 1 '), 1);
  }

  public function testGetTeamIds() {
    $this->compare(Import::getTeamIds('FF Warin'), array('59'));
    $this->compare(Import::getTeamIds('Team MV 1'), array('2'));
    $this->compare(Import::getTeamIds('Team MV 2'), array('2'));
    $this->compare(Import::getTeamIds('Team MV 3'), array('2'));
    $this->compare(Import::getTeamIds('Team MV 4'), array('2'));
    $this->compare(Import::getTeamIds('Team MV E'), array('2'));
    $this->compare(Import::getTeamIds('Team MV I'), array('2'));
    $this->compare(Import::getTeamIds('Team MV II'), array('2'));
    $this->compare(Import::getTeamIds('Team MV III'), array('2'));
    $this->compare(Import::getTeamIds('Team MV IV'), array('2'));
    $this->compare(Import::getTeamIds('FF Reinsdorf'), array('221', '1408', '1561'));
  }

  public function testGetTime() {
    $this->compare(Import::getTime('N'), false);
    $this->compare(Import::getTime('asdf'), false);
    $this->compare(Import::getTime('D'), null);
    $this->compare(Import::getTime('999'), false);
    
    $this->compare(Import::getTime('11,10'), null);
    $this->compare(Import::getTime('12,30'), 1230.0);
    $this->compare(Import::getTime('61,60'), 6160.0);
    $this->compare(Import::getTime('99,99'), 9999.0);
    $this->compare(Import::getTime('999,99'), null);
    
    $this->compare(Import::getTime('11:10'), null);
    $this->compare(Import::getTime('12:30'), 1230.0);
    $this->compare(Import::getTime('61:60'), 6160.0);
    $this->compare(Import::getTime('99:99'), 9999.0);
    $this->compare(Import::getTime('999:99'), null);
    
    $this->compare(Import::getTime('11;10'), null);
    $this->compare(Import::getTime('12;30'), 1230.0);
    $this->compare(Import::getTime('61;60'), 6160.0);
    $this->compare(Import::getTime('99;99'), 9999.0);
    $this->compare(Import::getTime('999;99'), null);
    
    $this->compare(Import::getTime('11.10'), null);
    $this->compare(Import::getTime('12.30'), 1230.0);
    $this->compare(Import::getTime('61.60'), 6160.0);
    $this->compare(Import::getTime('99.99'), 9999.0);
    $this->compare(Import::getTime('999.99'), null);

    $this->compare(Import::getTime('1:31:12'), 9112.0);
    $this->compare(Import::getTime('1:31,12'), 9112.0);
  }

  public function testGetCorrectClass() {
    $this->compare(Import::getCorrectClass(true), 'correct');
    $this->compare(Import::getCorrectClass(false), 'notcorrect');
  }

  public function testGetPersons() {
    $this->compare(Import::getPersons('Schmidt', 'Christian', 'male'), array(
      array('id' => '258', 'name' => 'Schmidt', 'firstname' => 'Christian', 'sex' => 'male'),
      array('id' => '1615', 'name' => 'Schmidt', 'firstname' => 'Christian', 'sex' => 'male')
    ));
    $this->compare(Import::getPersons('Limbach', 'Georg', 'male'), array(
      array('id' => '271', 'name' => 'Limbach', 'firstname' => 'Georg', 'sex' => 'male')
    ));
    $this->compare(Import::getPersons('Limbach', 'Georg', 'female'), array());
  }

  public function testGetOtherOfficialNames() {
    $this->compare(Import::getOtherOfficialNames(1652), array('Johannes Schubert'));
    $this->compare(Import::getOtherOfficialNames(283), array());
  }
}