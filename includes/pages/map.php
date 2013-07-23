<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.6.2/leaflet.css" />
<!--[if lte IE 8]>
    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.6.2/leaflet.ie.css" />
<![endif]-->

<script src="http://cdn.leafletjs.com/leaflet-0.6.2/leaflet.js"></script>

<script type="text/javascript">
/* <![CDATA[ */
$(function(){
$("#map").show();
var lat = 53.804503188488, lon = 11.710567474365234;

var osmUrl="http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
var osmAttrib='Map data © <a href="http://openstreetmap.org?lat='+lat+'&lon='+lon+'&zoom=12">openstreetmap</a>';
var osm = new L.TileLayer(osmUrl,{attribution: osmAttrib});

var fireUrl='http://openfiremap.org/hytiles/{z}/{x}/{y}.png';
var fireAttrib='<a href="http://openfiremap.org/?zoom=12&lat='+lat+'&lon='+lon+'">openfiremap</a>';
var fire = new L.TileLayer(fireUrl,{attribution: fireAttrib});

<?php
$rows = $db->getRows("
    SELECT `lat`,`lon`,`name`,`id`
    FROM `teams`
    WHERE `lon` IS NOT NULL
    AND `lat` IS NOT NULL
");

foreach ($rows as $key => $row) {
    $rows[$key]['name'] = Link::team($row['id'], $row['name']);
}


echo 'var teams = '.json_encode($rows).';';

?>

var map = new L.Map('map', {
        center: new L.LatLng(lat, lon),
        zoom:8,
        layers: [osm, fire]
    });

var i;
for(i = 0; i < teams.length; i++) {
    L.marker([teams[i].lat, teams[i].lon]).bindPopup(teams[i].name).addTo(map);
}
});
</script>
<div style="height: 500px;" id="map"></div>
