new SortTable(direction: "desc", noSorting: 5, selector: ".datatable-hb,.datatable-hl,.datatable-gs,.datatable-la,.datatable-fs")
new SortTable(direction: "desc", noSorting: 6, selector: ".datatable-zk")
new SortTable(direction: "desc", sortCol: 1, noSorting: 2, selector: ".datatable-teammates")

$('#report-error').click (ev) ->
  ev.preventDefault()
  personId = $(this).data('person-id')
  Fss.checkLogin () ->
    options = [
      { value: 'wrong', display: 'Person ist falsch geschrieben'}
      { value: 'other', display: 'Etwas anderes'}
    ]
    FssWindow.build('Auswahl des Fehlers')
    .add(new FssFormRowDescription('Bitte wählen Sie das Problem aus:'))
    .add(new FssFormRowRadio('what', 'Was ist passiert?', null, options))
    .on('submit', (data) ->
      selected = data.what
      if selected is 'wrong'
        options = [
          { value: 'together', display: 'Richtige Schreibweise auswählen (für Administrator <i>VIEL</i> einfacher)'}
          { value: 'correction', display: 'Selbst korrekte Schreibweise hinzufügen'}
        ]
        FssWindow.build('Korrektur des Fehlers')
        .add(new FssFormRowDescription('Bitte wählen Sie die Korrekturmethode aus:'))
        .add(new FssFormRowRadio('what', 'Korrektur wählen', null, options))
        .on('submit', (data) ->
          selected = data.what
          if selected is 'correction'
            Fss.getPerson personId, (person) ->
              FssWindow.build('Namen korrigieren')
              .add(new FssFormRowDescription('Bitte korrigieren Sie den Namen:'))
              .add(new FssFormRowText('firstname', 'Vorname', person.firstname))
              .add(new FssFormRowText('name', 'Name', person.name))
              .on('submit', (data) ->
                data.reason = selected
                data.type = 'person'
                data.personId = personId
                Fss.addError(data)
              )
              .open()
          else if selected is 'together'
            Fss.post 'get-persons', {}, (data) ->
              options = []
              for person in data.persons
                continue if person.id is personId
                options.push
                  value: person.id
                  display: "#{person.name}, #{person.firstname} (#{Fss.sexes[person.sex]})"

              FssWindow.build('Namen korrigieren')
              .add(new FssFormRowDescription('Bitte wählen Sie die korrekte Person aus:'))
              .add(new FssFormRowSelect('newPersonId', 'Richtige Person', null, options))
              .on('submit', (data) ->
                data.reason = selected
                data.type = 'person'
                data.personId = personId
                Fss.addError(data)
              )
              .open()
          )
          .open()
      else if selected is 'other'
        FssWindow.build('Fehler beschreiben')
        .add(new FssFormRowDescription('Bitte beschreiben Sie das Problem:'))
        .add(new FssFormRowTextarea('description', 'Beschreibung', ''))
        .on('submit', (data) ->
          data.reason = selected
          data.type = 'person'
          data.personId = personId
          Fss.addError(data)
        )
        .open()
    )
    .open()