#= require FssMap

new SortTable(noSorting: 10)


$('#map-load').click (ev) ->
  button = $(this)
  loadRow = button.closest('.row').addClass('hide')
  mapRow = $('#map-dynamic').closest('.row').removeClass('hide')
  lat = button.data('lat')
  lon = button.data('lon')
  placeId = button.data('place-id')
  placeName = button.data('place-name')

  mapEdit = $('#map-edit')
  mapSave = $('#map-save').hide()
  mapEditHint = $('#map-edit-hint').hide()

  w = new WaitFssWindow()
  FssMap.loadStyle () ->
    w.close()
    loaded = true
    unless lat? || lon?
      lat = FssMap.lat
      lon = FssMap.lon
      loaded = false
    map = FssMap.getMap('map-dynamic', 8, lat, lon)

    marker = L.marker([lat, lon]).bindPopup(placeName).addTo(map)

    handleMap = () ->
      latlng = marker.getLatLng()
      editMarker = L.marker(latlng, {draggable: true})
      map.removeLayer(marker).addLayer(editMarker)
      mapEdit.hide()
      mapEditHint.show()
      mapSave.show().click (ev) ->
        Fss.checkLogin () ->
          Fss.postReload 'set-place-location',
            lat: editMarker.getLatLng().lat
            lon: editMarker.getLatLng().lng
            placeId: placeId

    if loaded
      mapEdit.show().click (ev) -> handleMap()
    else
      handleMap()
