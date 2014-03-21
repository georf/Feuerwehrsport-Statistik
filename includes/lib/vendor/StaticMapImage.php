<?php

/**
 * staticMapLite 0.02
 *
 * Copyright 2009 Gerhard Koch
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Gerhard Koch <gerhard.koch AT ymail.com>
 *
 */ 

class StaticMapImage {
  private $tileCache = array();

  private $tileSize = 256;
  private $tileSrcUrl = "http://b.tile.openstreetmap.de/tiles/osmde/{Z}/{X}/{Y}.png";
  
  private $zoom, $lat, $lon, $width, $height, $markers, $image;
  private $centerX, $centerY, $offsetX, $offsetY;

  public function __construct($lat, $lon, $zoom) {
    $this->zoom = $zoom;
    $this->lat = $lat;
    $this->lon = $lon;
    $this->width = 500;
    $this->height = 400;
  }

  private function lonToTile($long, $zoom) {
    return (($long + 180) / 360) * pow(2, $zoom);
  }

  private function latToTile($lat, $zoom) {
    return (1 - log(tan($lat * pi()/180) + 1 / cos($lat* pi()/180)) / pi()) /2 * pow(2, $zoom);
  }

  private function initCoords() {
    $this->centerX = $this->lonToTile($this->lon, $this->zoom);
    $this->centerY = $this->latToTile($this->lat, $this->zoom);
    $this->offsetX = floor((floor($this->centerX)-$this->centerX)*$this->tileSize);
    $this->offsetY = floor((floor($this->centerY)-$this->centerY)*$this->tileSize);
  }

  private function createBaseMap() {
    $this->image = imagecreatetruecolor($this->width, $this->height);
    $startX = floor($this->centerX-($this->width/$this->tileSize)/2);
    $startY = floor($this->centerY-($this->height/$this->tileSize)/2);
    $endX = ceil($this->centerX+($this->width/$this->tileSize)/2);
    $endY = ceil($this->centerY+($this->height/$this->tileSize)/2);
    $this->offsetX = -floor(($this->centerX-floor($this->centerX))*$this->tileSize);
    $this->offsetY = -floor(($this->centerY-floor($this->centerY))*$this->tileSize);
    $this->offsetX += floor($this->width/2);
    $this->offsetY += floor($this->height/2);
    $this->offsetX += floor($startX-floor($this->centerX))*$this->tileSize;
    $this->offsetY += floor($startY-floor($this->centerY))*$this->tileSize;

    for ($x = $startX; $x <= $endX; $x++) {
      for ($y = $startY; $y <= $endY; $y++) {
        $url = str_replace(array('{Z}','{X}','{Y}'), array($this->zoom, $x, $y), $this->tileSrcUrl);
        $tileImage = imagecreatefromstring($this->fetchTile($url));
        $destX = ($x-$startX) * $this->tileSize + $this->offsetX;
        $destY = ($y-$startY) * $this->tileSize + $this->offsetY;
        imagecopy($this->image, $tileImage, $destX, $destY, 0, 0, $this->tileSize, $this->tileSize);
      }
    }
  }

  private function placeMarkers() {
    global $config;

    $markerImg = imagecreatefrompng($config['base'].'styling/images/marker.png');
    $destX = floor(($this->width  / 2) - $this->tileSize * ($this->centerX - $this->lonToTile($this->lon, $this->zoom)));
    $destY = floor(($this->height / 2) - $this->tileSize * ($this->centerY - $this->latToTile($this->lat, $this->zoom)));
    $destX = $destX - (imagesx($markerImg) / 2);
    $destY = $destY - (imagesy($markerImg) / 2);

    imagecopy($this->image, $markerImg, $destX, $destY, 0, 0, imagesx($markerImg), imagesy($markerImg));
  }
  
  private function fetchTile($url) {
    if (isset($this->tileCache[$url])) return $this->tileCache[$url];

    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0");
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $tile = curl_exec($ch);
    if (curl_errno($ch)) throw new CurlException(curl_error($ch));
    curl_close($ch);

    $this->tileCache[$url] = $tile;
    return $tile;
  }

  private function copyrightNotice() {
    global $config;

    $imageX = imagesx($this->image);
    $imageY = imagesy($this->image);
    $logoImg = imagecreatefrompng($config['base']."styling/images/osm.png");
    $ccImg = imagecreatefrompng($config['base']."styling/images/cc-by-sa.png");
    imagecopy($this->image, $logoImg, $imageX - 40, $imageY - 40, 0, 0, 40, 40);
    imagecopy($this->image, $ccImg, $imageX - 123, $imageY - 18, 0, 0, 80, 15);
  }

  public function makeMap($filename) {
    try {
      $this->initCoords();    
      $this->createBaseMap();
      $this->placeMarkers();
      $this->copyrightNotice();
      return imagepng($this->image, $filename, 9);
    } catch (Exception $e) {
      return false;
    }
  }
}

class CurlException extends Exception {}