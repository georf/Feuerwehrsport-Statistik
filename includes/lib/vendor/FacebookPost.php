<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;

FacebookSession::setDefaultApplication($config['facebook']['app-id'], $config['facebook']['secret']);

class FacebookPost {
  public function __construct($message, $link = false) {
    global $config;

    $params = array('message' => $message);
    if ($link) $params['link'] = $link;
    $session = new FacebookSession($config['facebook']['page-access']);
    $request = new FacebookRequest($session, 'POST', '/feed', $params);
    $response = $request->execute();
    $graphObject = $response->getGraphObject();
  }
}
