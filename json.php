<?php

$output = array('login' => false);

try {
  require_once(__DIR__.'/includes/lib/init.php');

  $output['login'] = Login::check();
  $output['success'] = true;

  $_type = Check2::except()->get('type')->getVal();

  if ($_type === 'login') {
    $output['login'] = Login::in(
      Check2::except()->post('name')->present(),
      Check2::except()->post('email')->getVal(),
      Check2::except()->server('REMOTE_ADDR')->getVal(),
      Check2::except()->server('HTTP_USER_AGENT')->getVal()
    );
  }
  
  if (Login::check() && preg_match('/^((get)|(set)|(add)|(delete))-(.+)$/', $_type, $result)) {
    $type = $result[1];
    $request = $result[6];


    Check2::except()->variable($type)->isIn(array('set', 'get', 'add', 'delete'));
    if (false && !Check::isAdmin() && $type != 'get') {
      $output['success'] = false;
      $output['message'] = "Zur Zeit kann aufgrund von Umbauarbeiten keine Bearbeitungen vorgenommen werden. Bitte versuche es in ein paar Tagen erneut.";
    } else {
      include Check2::except()->variable($request)->isInPath(__DIR__.'/includes/api/'.$type.'/');
    }
  }
} catch (Exception $e) {
  $output['success'] = false;
  $output['message'] = $e->getMessage();
  $output['trace']   = $e->getTrace();
}
echo json_encode($output);