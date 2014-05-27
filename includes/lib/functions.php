<?php
function __autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require __DIR__.'/vendor/'.$fileName;
}



function laType($key) {
    global $config;
    return (isset($config['la'][$key])) ?  $config['la'][$key] : '';
}



function fsType($key) {
    global $config;
    return (isset($config['fs'][$key])) ?  $config['fs'][$key] : '';
}


function post($key)
{
  if (isset($_POST[$key])) {
    return $_POST[$key];
  }
  return '';
}

function infomail($text) {}


function getWk($wk) {
	$wks = array(
        "B-Schlauch",
        "Verteiler",
        "C-Schlauch",
        "Knoten",
        "D-Schlauch",
        "Läufer"
	);
	if (isset($wks[$wk-1])) {
		return $wks[$wk-1];
	} else {
		return '';
	}
}


function getLWK($wk) {
	$wks = array(
        "Maschinist",
        "A-Länge",
        "Saugkorb",
        "B-Schlauch",
        "Strahlrohr links",
        "Verteiler",
        "Strahlrohr rechts"
	);
	if (isset($wks[$wk-1])) {
		return $wks[$wk-1];
	} else {
		return '';
	}
}

function getFSmWK($wk) {
	$wks = array(
        "Haus",
        "Wand",
        "Balken",
        "Feuer"
	);
	if (isset($wks[$wk-1])) {
		return $wks[$wk-1];
	} else {
		return '';
	}
}
function getFSwWK($wk) {
	$wks = array(
        "Leiterwand",
        "Hürde",
        "Balken",
        "Feuer"
	);
	if (isset($wks[$wk-1])) {
		return $wks[$wk-1];
	} else {
		return '';
	}
}

function notFound()
{
  return '
    <div class="container row">
      <div class="five columns not-found"></div>
      <div class="eleven columns">
        <h1>Seite nicht gefunden</h1>
        <p>Sie sind mit einem veralteten Link auf diese Seite gekommen.</p>
        <ul class="disc">
          <li><a href="/page-home.html">Startseite</a></li>
          <li><a href="/page-home.html#kontakt">Kontakt</a></li>
          <li><a href="/page-home.html#fehler">Fehler melden</a></li>
        </ul>
      </div>
    </div>';
}

function gDate($date)
{
    return date('d.m.Y', strtotime($date));
}


function dataAttrs($object, $namespace = '') {
    $output = '';

    if (!empty($namespace)) $namespace .= '-';
    foreach ($object as $key => $value) {
        $output .= ' data-'.$namespace.$key.'="'.$value.'" ';
    }
    return $output;
}

function dataDiv($object, $namespace = '') {
    return '<div'.dataAttrs($object, $namespace).' style="display:none;" id="global-data-object"></div>';
}



function c2s($centi) {
    return intval($centi)/100;
}

function time2stringD($centi) {
    if (!$centi) return 'D';
    else return time2string($centi);
}

function time2string($centi) {
    return sprintf('%.2f', c2s($centi));
}

function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

function getStatusColor($status) {
    $c = array(
        '#90EE90',
        '#D0FF74',
        '#FFFF00',
        '#FFD700',
        '#FFA500',
        '#FF7979'
    );

    return 'background:'.$c[intval($status)];
}

function getDiscipline($id) {
    return ($id == 2)? 'Hindernisbahn' : 'Hakenleitersteigen';
}
