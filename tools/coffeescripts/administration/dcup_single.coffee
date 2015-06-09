$ ->
  $('button.add-youth').click () ->
    competitionId = $(this).data('competition-id')
    dcupId = $(this).data('dcup-id')

    Fss.post 'get-dcup-persons', competitionId: competitionId, (data) ->

      window = FssWindow.build('U20 auswählen')
      for person in data.persons
        window.add(new FssFormRowCheckbox("person_#{person.id}", "#{person.firstname} #{person.name}"))
      window
      .on('submit', (data) ->
        ids = []
        for person_id, youth of data
          result = person_id.match(/^person_(\d+)$/)
          ids.push(result[1]) if result && youth

        Fss.post 'add-dcup-youth-persons', competitionId: competitionId, personIds: ids.join(","), dcupId: dcupId, (data) ->
          AlertFssWindow.build("Eingefügt", "okay")
      )
      .open()
