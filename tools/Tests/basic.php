<?php

class StubbedDatabase extends Database {
  public function insertRow($table, $values, $cleanCache = true) {
    $row = $this->getFirstRow("
      SELECT *
      FROM `".$table."`
      LIMIT 1
    ");
    return $row['id'];
  }

  public function updateRow($table, $id, $values, $colName = 'id') {
  }
}

abstract class TestCase {
  protected $errors = array();

  public function __construct() {
    global $db, $config;
    $db = new StubbedDatabase(
      $config['database']['server'],
      $config['database']['database'],
      $config['database']['username'],
      $config['database']['password']
    );

    $config['error-mail'] = '';
    $config['no-clean'] = true;
  }

  public static function run() {
    $className = get_called_class();
    $test = new $className();
    $test->runTests();
    return $test->errors;
  }

  protected function runTests() {
    $reflClass = new ReflectionClass(get_called_class());
    $methods = $reflClass->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
      if ($method->isStatic()) continue;
      try {
        $methodName = $method->name;
        $this->$methodName();
        echo '.';
      } catch(Exception $e) {
        if ($e instanceof TestException) {
          $e->setMethod($method);
        }
        $this->errors[] = $e;
        echo 'F';
      }
    }
  }

  protected function throwsException($func) {
    try {
      $func();
    } catch(Exception $e) {
      return true;
    }
    throw new TestException();
  }

  protected function validRow($table) {
    global $db;
    return $db->getFirstRow("
      SELECT *
      FROM `".$table."`
      LIMIT 1
    ");
  }

  protected function isTrue($value) {
    $this->compare($value, true);
  }

  protected function isFalse($value) {
    $this->compare($value, false);
  }

  protected function isNull($value) {
    $this->compare($value, null);
  }

  protected function isFieldSet($array, $field) {
    $this->isArray($array);
    if (!isset($array[$field])) {
      throw new TestException("%s is not set in array", $field);
    }
    return true;
  }

  protected function isFieldNotSet($array, $field) {
    $this->isArray($array);
    if (isset($array[$field])) {
      throw new TestException("%s is set in array", $field);
    }
    return true;
  }

  protected function isArray($value) {
    if (!is_array($value)) {
      throw new TestException("%s is not an array", $value);
    }
    return true;
  }

  protected function compare($value, $given) {
    if ($value !== $given) {
      throw new TestException("%s is not %s", $value, $given);
    }
    return true;
  }
}

abstract class ApiTestCase extends TestCase {
  protected function api($options, $loggedIn = true) {
    global $db, $config, $_GET, $_POST, $_SERVER, $_SESSION;

    $_GET     = isset($options['get'])     ? $options['get']     : array();
    $_POST    = isset($options['post'])    ? $options['post']    : array();
    $_SERVER  = isset($options['server'])  ? $options['server']  : array();
    $_SESSION = isset($options['session']) ? $options['session'] : array();

    if ($loggedIn) {
      $_SESSION['login']      = '111';
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }


    ob_start();
    include(__DIR__.'/../../json.php');
    $content = ob_get_contents();
    ob_end_clean();
    $json = json_decode($content, true);
    if ($json === null) throw new TestException("no valid json response");
    return $json;
  }
  protected function apiPost($type, $request, $params, $loggedIn = true) {
    return $this->api(array(
      'get'  => array('type' => $type.'-'.$request),
      'post' => $params,
    ), $loggedIn);
  }

  protected function apiGet($type, $request, $loggedIn = true) {
    return $this->api(array(
      'get' => array('type' => $type.'-'.$request)
    ), $loggedIn);
  }

  protected function success($response, $fields = array()) {
    $fields[] = 'success';
    foreach ($fields as $field) {
      $this->isFieldSet($response, $field);
    }
    return $this->isTrue($response['success']);
  }

  protected function failed($response, $fields = array()) {
    foreach ($fields as $field) {
      $this->isFieldNotSet($response, $field);
    }
    return $this->isFalse($response['success']); 
  }
}

class TestException extends Exception {
  protected $method;

  public function __construct() {
    $arguments = func_get_args();
    if (count($arguments) > 0) {
      $message = $arguments[0];
      for ($i=1; $i < count($arguments); $i++) {
        $message = preg_replace('/%s/', $this->escape($arguments[$i]), $message, 1);
      }
      parent::__construct($message);
    } else {
      parent::__construct();
    }
  }
  public function setMethod($method) {
    $this->method = $method;
  }
  public function escape($value) {
    if (is_bool($value)) return $value ? 'true' : 'false';
    if (is_null($value)) return 'null';
    if (is_array($value)) return json_encode($value);
    return strval($value);
  }

  public function __toString() {
    return sprintf(
      "\n ===> %s::%s\n ===> %s\n\n%s", 
      $this->method->class, 
      $this->method->name, 
      $this->getMessage(), 
      $this->getTraceAsString()
    );
  }
}