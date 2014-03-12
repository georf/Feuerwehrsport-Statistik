<?php

class DcupCalculationTest extends TestCase {
  public function testSortSingle() {
    $this->compare(DcupCalculation::sortSingle(array()), array());
    
    $this->compare(DcupCalculation::sortSingle(array(
      array('time' => '2000', 'other' => array('2002'), 'id' => '1'),
      array('time' => '2000', 'other' => array('2001'), 'id' => '2'),
      array('time' => '2000', 'other' => array('2000'), 'id' => '3'),
    )), array(
      array('time' => '2000', 'other' => array('2000'), 'id' => '3'),
      array('time' => '2000', 'other' => array('2001'), 'id' => '2'),
      array('time' => '2000', 'other' => array('2002'), 'id' => '1'),
    ));
    
    $this->compare(DcupCalculation::sortSingle(array(
      array('time' => '2000', 'other' => array('2002'), 'id' => '1'),
      array('time' => '2000', 'other' => array(), 'id' => '2'),
      array('time' => '2000', 'other' => array('2000'), 'id' => '3'),
    )), array(
      array('time' => '2000', 'other' => array('2000'), 'id' => '3'),
      array('time' => '2000', 'other' => array('2002'), 'id' => '1'),
      array('time' => '2000', 'other' => array(), 'id' => '2'),
    ));
    
    $this->compare(DcupCalculation::sortSingle(array(
      array('time' => '2000', 'other' => array('2002', '2004'), 'id' => '1'),
      array('time' => '2000', 'other' => array('2002', '2003', '2005'), 'id' => '2'),
      array('time' => '2000', 'other' => array(), 'id' => '3'),
      array('time' => '2000', 'other' => array('2002', '2003', '2004'), 'id' => '4'),
    )), array(
      array('time' => '2000', 'other' => array('2002', '2003', '2004'), 'id' => '4'),
      array('time' => '2000', 'other' => array('2002', '2003', '2005'), 'id' => '2'),
      array('time' => '2000', 'other' => array('2002', '2004'), 'id' => '1'),
      array('time' => '2000', 'other' => array(), 'id' => '3'),
    ));
  }
}