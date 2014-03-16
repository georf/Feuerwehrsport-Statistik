#= require FssMap

new SortTable(selector: ".datatable-sort-members", noSorting: 8)
new SortTable(selector: ".datatable-sort-competitions", noSorting: 7, direction: "desc")
new SortTable(selector: ".datatable-sort-team-scores", noSorting: [3,4], direction: "desc")
new SortTable(selector: ".scores-gs", noSorting: [5..11], direction: "desc")
new SortTable(selector: ".scores-fs", noSorting: [5..9], direction: "desc")
new SortTable(selector: ".scores-la", noSorting: [5..12], direction: "desc")

$('#report-error').click (ev) ->
  ev.preventDefault()
  teamId = $(this).data('team-id')

  Fss.checkLogin () ->
    options = [
      { value: 'together', display: 'Team ist doppelt vorhanden'},
      { value: 'correction', display: 'Team ist falsch geschrieben'},
      { value: 'other', display: 'Etwas anderes'}
    ]
    FssWindow.build('Auswahl des Fehlers')
    .add(new FssFormRowDescription('Bitte wählen Sie das Problem aus:'))
    .add(new FssFormRowRadio('what', 'Was ist passiert?', null, options))
    .on('submit', (data) ->
      selected = data.what

      if selected is 'correction'
        Fss.getTeam teamId, (team) ->
          options = [
            { value: 'Team', display: 'Zusammenschluss (Team)'}
            { value: 'Feuerwehr', display: 'Einzelne Feuerwehr'}
          ]
          FssWindow.build('Namen korrigieren')
          .add(new FssFormRowDescription('Bitte korrigieren Sie den Namen:'))
          .add(new FssFormRowText('name', 'Name', team.name))
          .add(new FssFormRowText('short', 'Abkürzung', team['short']))
          .add(new FssFormRowDescription('Kurzer Name (maximal 10 Zeichen)'))
          .add(new FssFormRowSelect('teamType', 'Typ der Mannschaft', team.type, options))
          .on('submit', (data) ->
            data.reason = selected
            data.type = 'team'
            data.teamId = teamId
            Fss.addError(data)
          )
          .open()
      else if selected is 'together'
        Fss.getTeams null, (teams) ->
          for team, i in teams
            if team.value is teamId
              teams.splice(i, 1)
              break

          FssWindow.build('Namen korrigieren')
          .add(new FssFormRowDescription('Bitte wählen Sie das korrekte Team aus:'))
          .add(new FssFormRowSelect('newTeamId', 'Richtiges Team:', null, teams))
          .on('submit', (data) ->
            data.reason = selected
            data.type = 'team'
            data.teamId = teamId
            Fss.addError(data)
          )
          .open()
      else if selected is 'other'
        FssWindow.build('Fehler beschreiben')
        .add(new FssFormRowDescription('Bitte beschreiben Sie das Problem:'))
        .add(new FssFormRowTextarea('description', 'Beschreibung', ''))
        .on('submit', (data) ->
          data.reason = selected
          data.type = 'team'
          data.teamId = teamId
          Fss.addError(data)
        )
        .open()
    )
    .open()


$('#map-load').click (ev) ->
  button = $(this)
  loadRow = button.closest('.row').addClass('hide')
  mapRow = $('#map-dynamic').closest('.row').removeClass('hide')
  lat = button.data('lat')
  lon = button.data('lon')
  teamId = button.data('team-id')
  teamName = button.data('team-name')

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

    marker = L.marker([lat, lon]).bindPopup(teamName).addTo(map)

    handleMap = () ->
      latlng = marker.getLatLng()
      editMarker = L.marker(latlng, {draggable: true})
      map.removeLayer(marker).addLayer(editMarker)
      mapEdit.hide()
      mapEditHint.show()
      mapSave.show().click (ev) ->
        Fss.checkLogin () ->
          Fss.postReload 'set-team-location',
            lat: editMarker.getLatLng().lat
            lon: editMarker.getLatLng().lng
            teamId: teamId

    if loaded
      mapEdit.show().click (ev) -> handleMap()
    else
      handleMap()
