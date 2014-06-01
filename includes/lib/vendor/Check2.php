<?php

class CheckException extends Exception {
  public function __construct($name, $value) {
    if ($value && is_array($value)) {
      parent::__construct($name.' not found in '.implode(", ", $value));
    } elseif ($value) {
      parent::__construct($name.' not found in '.$value);
    } else {
      parent::__construct($name.' not found');
    }
  }
}

class Check2 {
  public static function except() {
    return new self('except');
  }

  public static function page() {
    return new self('page');
  }

  public static function boolean() {
    return new self('boolean');
  }

  public static function value() {
    $check = new self('value');
    $arguments = func_get_args();
    if (count($arguments) > 0) {
      $check->default = $arguments[0];
      $check->defaultSet = true;
    }
    return $check;
  }
  
  private $type = false;
  private $value = false;
  private $name = "";
  private $default = null;
  private $defaultSet = false;

  private function __construct($type) {
    $this->type = $type;
  }

  private function valueIn($name, $array) {
    $this->name = $name;
    if (isset($array[$name])) {
      $this->value = $array[$name];
    } else {
      $this->value = false;
      $this->escape();
    }
    return $this;
  }

  public function isAdmin() {
    $this->name  = "admin";
    $this->value = true;
    return $this->escape(isset($_SESSION['loggedin']));
  }

  private function escape($result = false, $subject = false) {
    if ($this->type === 'except') {
      if ($result === false) throw new CheckException($this->name, $subject);
      return $this->value;
    } elseif ($this->type === 'page') {
      if ($result === false) throw new PageNotFound($this->name, $subject);
      return $this->value;
    } elseif ($this->type === 'boolean') {
      return ($result) ? true : false ;
    } else {
      if ($result === false) {
        return ($this->defaultSet) ? $this->default : false;
      }
      return $this->value;
    }
  }

  public function variable($content) {
    $this->value = $content;
    return $this;
  }

  public function get($name) {
    return $this->valueIn($name, $_GET);
  }

  public function post($name) {
    return $this->valueIn($name, $_POST);
  }

  public function server($name) {
    return $this->valueIn($name, $_SERVER);
  }

  public function isIn($value, $nullable = false) {
    if (is_array($value)) {
      return $this->escape(in_array($this->value, $value), $value);
    } else {
      global $db;
      $result = $db->getFirstRow("
        SELECT `id` 
        FROM `".$value."` 
        WHERE `id` = '".$db->escape($this->value)."' 
        LIMIT 1;"
      );
      if ($nullable === true && !$result) {
        return null;
      }
      if ($nullable === 'row' && $result) {
        return FSS::tableRow($value, $this->value);
      }
      return $this->escape($result, $value);
    }
  }

  public function getVal() {
    return $this->value;
  }

  public function isDate() {
    return $this->match('/^\d{4}-\d{2}-\d{2}$/');
  }

  public function present() {
    return $this->escape(!empty($this->value));
  }

  public function isSex() {
    return $this->isIn(array('male', 'female'));
  }

  public function match($regex) {
    return $this->escape(preg_match($regex, $this->value) === 1);
  }

  public function isDiscipline() {
    return $this->isIn(FSS::$disciplines);
  }

  public function isInPath($path) {
    $found = false;
    $vz = opendir($path);
    while ($file = readdir($vz)) {
      if ($file === $this->value.'.php') {
        closedir($vz);
        return $path.$file;
      }
    }
    closedir($vz);
    return $this->escape(false, $path);
  }

  public function isArray() {
    return $this->escape(is_array($this->value));
  }

  public function isNumber() {
    return $this->escape(is_numeric($this->value));
  }

  public function isTrue($value) {
    return $this->escape($value);
  }

  public function fullKey($orThis = false) {
    if ($this->value === $orThis) {
      return $this->escape($this->value);
    }
    $this->value = FSS::extractFullKey($this->value);
    if (is_array($this->value))
      $this->value = (in_array($this->value['key'], FSS::$disciplinesWithDoubleEvent)) ? $this->value : false;
    if (is_array($this->value) && $this->value['sex'])
    $this->value = (in_array($this->value['sex'], FSS::$sexes)) ? $this->value : false; 
    return $this->escape($this->value);
  }
}
