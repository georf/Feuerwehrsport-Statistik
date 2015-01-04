#!/usr/bin/php
<?php (PHP_SAPI === 'cli') || exit();

try {
  require_once(__DIR__.'/../includes/lib/init.php');
} catch (Exception $e) {
  die($e->getMessage());
}

$options = getopt("", array("dates", "competitions", "conclusion"));

if (isset($options["conclusion"]) && intval(date('z')) % 5 == 0) {
  $days = 5;
  $logs = array();
  foreach ($db->getRows("
    SELECT *
    FROM `logs`
    WHERE `inserted` BETWEEN 
      '".date('Y-m-d H:i:s', mktime(9, 0, 0, date("n"), date("j") - $days, date("Y")))."'
    AND
      '".date('Y-m-d H:i:s', mktime(9, 0, 0, date("n"), date("j"), date("Y")))."'
    ORDER BY `inserted` DESC
  ") as $log) {
    $logs[] = Log::getByRow($log);
  }

  $groups = Log::groupByType($logs);
  $groups = array_filter($groups, function($elem) { return $elem->showContent(); });
  if (count($groups) > 0) {
    $output = "In den letzten Tagen ist folgendes passiert:\n\n * ".implode("\n * ", $groups)."\n\nViel Spaß beim Stöbern";
    new FacebookPost($output, "http://www.feuerwehrsport-statistik.de/");
  }
}

if (isset($options["dates"])) {
  $date = $db->getFirstRow("
    SELECT *
    FROM `dates`
    WHERE `created_at` < '".date('Y-m-d H:i:s', time() - 3600)."'
    AND `published` = 0
    ORDER BY `created_at`
    LIMIT 1
  ");
  if ($date) {
    $hints = array();
    $hints[] = "Datum: ".gDate($date['date']);
    if ($date['name']) $hints[] = "Name: ".$date['name'];
    if ($date['place_id']) {
      $place = FSS::tableRow('places', $date['place_id']);
      $hints[] = "Ort: ".$place['name'];
    }
    if ($date['event_id']) {
      $event = FSS::tableRow('events', $date['event_id']);
      $hints[] = "Typ: ".$event['name'];
    }
    if ($date['disciplines']) {
      $hints[] = "Disziplinen: ".$date['disciplines'];
    }

    $output = "Es wurde ein neuer Wettkampftermin hinzugefügt: \n\n".implode("\n", $hints)."\n";
    $db->updateRow('dates', $date['id'], array('published' => 1), 'id', false);
    new FacebookPost($output, "http://www.feuerwehrsport-statistik.de/page/date-".$date['id'].".html");
  }
}


if (isset($options["competitions"])) {
  $competition = $db->getFirstRow("
    SELECT `id`
    FROM `competitions`
    WHERE `created_at` < '".date('Y-m-d H:i:s', time() - 3600)."'
    AND `published` = 1
    ORDER BY `created_at`
    LIMIT 1
  ");
  if ($competition) {
    $competition = FSS::competition($competition['id']);
    $calculation = CalculationCompetition::build($competition);
    $disciplines = $calculation->disciplines();

    $hints = array();
    if ($competition['name']) {
      $hints[] = 'Name: '.$competition['name'];
    }
    $hints[] = 'Datum: '.gdate($competition['date']);
    $hints[] = 'Austragungsort: '.$competition['place'];
    $hints[] = 'Typ: '.$competition['event'];

    if ($calculation->countSingleScores() && $competition['score_type']) {
      $hints[] = 'Mannschaftswertung: '.$competition['persons'].'/'.$competition['run'].'/'.$competition['score'];
    }
    if ($competition['la']) {
      $hints[] = 'Löschangriff: '.FSS::laType($competition['la']);
    }
    if ($competition['fs']) {
      $hints[] = '4x100m: '.FSS::fsType($competition['fs']);
    }

    if ($calculation->exists('hb')) {
      $hints[] = 'Hindernisbahn: '.$calculation->countWithSex('hb');
    }
    for ($f = -5; $f < -1; $f++) { 
      if ($calculation->exists('hb', $f)) {
        $hints[] = 'Hindernisbahn '.FSS::finalName($f).': '.$calculation->countWithSex('hb', $f);
      }
    }
    if ($calculation->count('hl')) {
      $hints[] = 'Hakenleitersteigen: '.$calculation->count('hl').' Männer';
    }
    for ($f = -5; $f < -1; $f++) {
      if ($calculation->count('hl', null, $f)) {
        $hints[] = 'Hakenleitersteigen '.FSS::finalName($f).': '.$calculation->count('hl', null, $f).' Männer';
      }
    }
    if ($calculation->count('zk')) {
      $hints[] = 'Zweikampf: '.$calculation->count('zk').' Männer';
    }
    if ($calculation->count('gs')) {
      $hints[] = 'Gruppenstafette: '.$calculation->count('gs').' Frauenmannschaften';
    }
    if ($calculation->exists('fs')) {
      $hints[] = 'Feuerwehrstafette: '.$calculation->countWithSex('fs');
    }
    if ($calculation->exists('la')) {
      $hints[] = 'Löschangriff: '.$calculation->countWithSex('la');
    }

    $output = "Es wurde ein neuer Wettkampf hinzugefügt: \n\n".implode("\n", $hints)."\n";
    $db->updateRow('competitions', $competition['id'], array('published' => 2), 'id', false);
    new FacebookPost($output, "http://www.feuerwehrsport-statistik.de/page/competition-".$competition['id'].".html");
  }
}
