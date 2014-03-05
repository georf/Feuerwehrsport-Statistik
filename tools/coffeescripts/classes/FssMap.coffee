class @FssMap
  @styleLoaded: false
  @loaded: (callback) ->
    FssMap.styleLoaded = true
    callback()

  @lat: 51
  @lon = 13

  @loadStyle: (callback) ->
    return callback() if FssMap.styleLoaded

    $.getScript '/js/leaflet.js', () ->
      L.Icon.Default.imagePath = '/styling/images/'
      $.get '/css/leaflet.css', (css) ->
        $('<style type="text/css"></style>').html(css).appendTo("head")
        if $.browser.msie && parseInt($.browser.version, 10) < 8
          $.get '/css/leaflet.ie.css', (css) ->
            $('<style type="text/css"></style>').html(css).appendTo("head")
            FssMap.loaded(callback)
        else
          FssMap.loaded(callback)

  @getMap: (id, zoom, lat, lon) ->
    osmUrl = "http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
    osmAttrib = 'Map data © <a href="http://openstreetmap.org">openstreetmap</a>'
    osm = L.tileLayer(osmUrl, {attribution: osmAttrib})

    fireUrl = 'http://openfiremap.org/hytiles/{z}/{x}/{y}.png'
    fireAttrib = '<a href="http://openfiremap.org">openfiremap</a>'
    fire = L.tileLayer(fireUrl, {attribution: fireAttrib})
    return L.map id, 
      center: L.latLng(lat, lon)
      zoom: zoom
      layers: [osm, fire]