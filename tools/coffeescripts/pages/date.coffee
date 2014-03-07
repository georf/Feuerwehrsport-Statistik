new SortTable(noSorting: 5)

changeDate = (id, places, events, values = {}) ->
  placeOptions = [ value: 'NULL', display: '----' ]
  for place in places
    placeOptions.push value: place.id, display: place.name

  eventOptions = [ value: 'NULL', display: '----' ]
  for event in events
    eventOptions.push value: event.id, display: event.name

  if values.disciplines
    disciplineValues = {}
    for discipline in values.disciplines.split(',')
      disciplineValues[discipline.toLowerCase()] = true
    values = $.extend(disciplineValues, values)

  values = $.extend({placeId: values.place_id}, values) if values.place_id
  values = $.extend({eventId: values.event_id}, values) if values.event_id

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
      return changeDate(places, events, data)

    error =
      date: data
      reason: 'change'
      type: 'date'
      dateId: id
    Fss.addError(error)
  )
  .open()

$('#change-date').click () ->
  dateId = $(this).data('date-id')
  Fss.checkLogin () ->
    Fss.getPlaces (places) ->
      Fss.getEvents (events) ->
        Fss.post 'get-date', {dateId: dateId}, (data) ->
          changeDate(dateId, places, events, data.date)