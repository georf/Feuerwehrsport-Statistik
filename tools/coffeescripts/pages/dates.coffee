new SortTable(noSorting: 5)

addDate = (places, events, values = {}) ->
  placeOptions = [ value: 'NULL', display: '----' ]
  for place in places
    placeOptions.push value: place.id, display: place.name

  eventOptions = [ value: 'NULL', display: '----' ]
  for event in events
    eventOptions.push value: event.id, display: event.name

  defaultValues = 
    date: ""
    name: ""
    placeId: 'NULL'
    eventId: 'NULL'
    description: ""
    fs: false
    hb: false
    hl: false
    gs: false
    la: false
  values = $.extend(defaultValues, values)

  FssWindow.build('Termin hinzufügen')
  .add(new FssFormRowDate('date', 'Datum', values.date))
  .add(new FssFormRowText('name', 'Name', values.name))
  .add(new FssFormRowSelect('placeId', 'Ort', values.placeId, placeOptions))
  .add(new FssFormRowSelect('eventId', 'Typ', values.eventId, eventOptions))
  .add(new FssFormRowTextarea('description', 'Beschreibung', values.description))
  .add(new FssFormRowCheckbox('fs', 'Feuerwehrstafette', values.fs))
  .add(new FssFormRowCheckbox('hb', 'Hindernisbahn', values.hb))
  .add(new FssFormRowCheckbox('hl', 'Hakenleitersteigen', values.hl))
  .add(new FssFormRowCheckbox('gs', 'Gruppenstafette', values.gs))
  .add(new FssFormRowCheckbox('la', 'Löschangriff', values.la))
  .on('submit', (data) ->
    if data.name is '' or data.description is ''
      data.message = "Name und Beschreibung müssen gesetzt sein."
      return addDate(places, events, data)

    Fss.post 'add-date', data, (result) ->
      location.reload()
  )
  .open()

$('#add-date').click () ->
  Fss.checkLogin () ->
    Fss.getPlaces (places) ->
      Fss.getEvents (events) ->
        addDate(places, events)