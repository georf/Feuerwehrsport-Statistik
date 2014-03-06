new SortTable(noSorting: 6)

$('#add-person').click (ev) ->
  ev.preventDefault()
  Fss.checkLogin () ->
    options = [
      { value: 'male', display: 'männlich'}
      { value: 'female', display: 'weiblich'}
    ]

    FssWindow.build('Person hinzufügen')
    .add(new FssFormRowText('firstname', 'Vorname'))
    .add(new FssFormRowText('name', 'Name'))
    .add(new FssFormRowSelect('sex', 'Geschlecht', null, options))
    .on('submit', (data) ->
      Fss.post 'add-person', data, (result) ->
        location.reload()
    )
    .open()
