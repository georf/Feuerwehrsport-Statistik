reloadErrors = () ->
  Fss.post 'get-errors', {}, (data) ->
    table = $('#errors')
    table.children().remove()
    for e in data.errors
      continue if e.done_at
      error = new Error(e.id, e.user_id, e.content, e.created_at, e.email, e.name)
      table.append(error.getTr())


class Error
  constructor: (@id, @userId, @content, @createdAt, @creatorEmail, @creatorName) ->
    @headline = @content.type
    @openTrs = null
    @openType = () =>
    @isOpen = false
    switch @content.type
      when "competition" then @handleCompetition()
      when "person" then @handlePerson()
      when "team" then @handleTeam()
      when "date" then @handleDate()


  getTr: () =>
    creatorTd = $('<th/>').text(@creatorName)
    if @creatorEmail
      creatorTd.append(" (")
      creatorTd.append($('<a/>').attr("href", "mailto:#{@creatorEmail}").text(@creatorEmail))
      creatorTd.append(")")
    @tr = $('<tr/>')
      .append($('<th/>').text(@formatDateTime(@createdAt)))
      .append(creatorTd)
      .append($('<th/>').text(@headline))
      .append($('<th/>')
      .append($('<button/>').append($('<span/>').addClass("glyphicon glyphicon-chevron-down"))
      .on('click', @click)
      .css('cursor', 'pointer')
      ))
    
  click: () =>
    if @isOpen
      @openTrs.hide()
      @tr.removeClass('active').find('span').toggleClass('glyphicon-chevron-down glyphicon-chevron-up')
      @isOpen = false
    else
      @isOpen = true
      @tr.addClass('active').find('span').toggleClass('glyphicon-chevron-down glyphicon-chevron-up')
      unless @openTrs
        code = $('<tr/>').append($('<td/>').attr('colspan', 4).append($('<pre/>').text(JSON.stringify(@content))))
        div = $('<div/>').addClass("row")
        @openType(div)
        @openTrs = code.add($('<tr/>').append($('<td/>').attr('colspan', 4).append(div)))
        @tr.after(@openTrs)
      else
        @openTrs.show()

  box: (cols, appendTo = null) =>
    div = $('<div/>').addClass("col-md-#{cols}")
    div.appendTo(appendTo) if appendTo
    div

  getActionBox: (div, callback = null, cols = 3) =>
    box = @box(cols, div).append($('<button/>').text('Erledigt').click( () => @confirmDone("Ohne Aktion - ") ))
    if callback
      box.append('<br/>').append($('<button/>').text('Beheben').click( () => @confirmAction(callback) ))
    box

  confirmAction: (callback, text = "") ->
    new ConfirmFssWindow("Fehler wirklich beheben?", "Diese Aktion verändert die Daten! #{text}", () -> callback())    

  confirmDone: (text = "") =>
    new ConfirmFssWindow(text + 'Fehler erledigt', text + 'Wirklich als erledigt markieren?', () =>
      Fss.post 'set-error-done', { errorId: @id }, (data) -> reloadErrors()
    )

  handleCompetition: () =>
    getCompetitionBox = (appendTo, headline = "Wettkampf", id = @content.competitionId) =>
      box = @box(3, appendTo).append($('<h4/>').text(headline))
      Fss.getCompetition id, (competition) ->
        Fss.post 'get-place', placeId: competition.place_id, (data) ->
          place = data.place
          Fss.post 'get-event', eventId: competition.event_id, (data) ->
            event = data.event
            box.append(
              $('<a/>')
              .attr('href', "/page/competition-#{competition.id}.html")
              .text("#{competition.date} (#{competition.name})")
            )
            .append("<br/>ID: #{id}")
            .append("<br/>Ort: #{place.name}")
            .append("<br/>Typ: #{event.name}")
    @headline = "Wettkampf"
    switch @content.reason
      when "name"
        @headline += " - Name"
        @openType = (div) =>
          getCompetitionBox(div)
          @box(4, div)
            .append($('<h4/>').text("Korrektur"))
            .append("Name: #{@content.name}")
          @getActionBox(div)
          .append('<br/>')
          .append $('<button/>').text('Neuen Namen setzen').click () =>
            FssWindow.build('Namen eintragen')
            .add(new FssFormRowText('name', 'Name', @content.name))
            .on('submit', (data) =>
              @confirmAction () => 
                data.competitionId = @content.competitionId
                Fss.post 'set-competition-name', data, (d) => @confirmDone()
            )
            .open()
      when "hint"
        @headline += " - Hinweis"
        @openType = (div) =>
          appendHintToUl = (ul, hint) ->
            ul.append(
              $('<li/>')
              .text(hint.hint)
              .append($('<span/>').addClass("glyphicon glyphicon-remove")).css('cursor', 'pointer').click () ->
                new ConfirmFssWindow 'Hinweis löschen', 'Wirklich löschen?', () ->
                  Fss.post('delete-hint', competitionHintId: hint.id, () -> reloadHints())
                
            )
          reloadHints = () =>
            Fss.post 'get-hints', competitionId: @content.competitionId, (data) ->
              hintsBox.children().remove()
              hintsBox.append($('<h4/>').text("Vorhandene Hinweise"))
              ul = $('<ul/>').appendTo(hintsBox)
              console.log(data.hints)
              for hint in data.hints
                appendHintToUl(ul, hint)
                


          getCompetitionBox(div)
          @box(3, div).append($('<h4/>').text("Aktueller Hinweis")).append($('<pre/>').text(@content.description))
          hintsBox = @box(3, div)
          reloadHints()
          @getActionBox(div)
          .append('<br/>')
          .append($('<button/>').text("Hinweis hinzufügen").click () =>
            FssWindow.build('Hinweis hinzufügen')
              .add(new FssFormRowTextarea('hint', 'Hinweis', @content.description))
              .on('submit', (data) =>
                data.competitionId = @content.competitionId
                Fss.post 'add-hint', data, () -> reloadHints()
              )
              .open()
          )

  handlePerson: () =>
    getPersonBox = (appendTo, headline = "Person", id = @content.personId) =>
      box = @box(3, appendTo).append($('<h4/>').text(headline))
      Fss.getPerson id, (person) ->
        box.append(
          $('<a/>')
          .attr('href', "/page/person-#{person.id}.html")
          .text("#{person.firstname} #{person.name} (#{person.sex})")
        ).append("<br/>ID: #{id}")
    @headline = "Person"
    switch @content.reason
      when "correction"
        @headline += " - Korrektur"
        @openType = (div) =>
          getPersonBox(div)
          @box(4, div)
            .append($('<h4/>').text("Korrektur"))
            .append("Vorname: #{@content.firstname}<br/>Nachname: #{@content.name}")
          @getActionBox div, () =>
            Fss.post 'set-person-name', @content, (data) => @confirmDone()

      when "together"
        @headline += " - Zusammenführen"
        @openType = (div) =>
          action = (params = {}) =>
            params.newPersonId = @content.newPersonId
            params.personId = @content.personId
            Fss.post 'set-person-together', params, (data) => @confirmDone()
          getPersonBox(div)
          getPersonBox(div, "Richtige Person", @content.newPersonId)
          @getActionBox(div, () => action() )
          .append($('<button/>').text('Immer beheben').click( () => 
            @confirmAction(
              () -> action( always: true ),
              "Beim Import wird in Zukunft immer automatisch der Name ersetzt."
            )
          ))

      when "other"
        @headline += " - Freitext"
        @openType = (div) =>
          getPersonBox(div)
          @box(5, div).append($('<pre/>').text(@content.description))
          @getActionBox(div)

  handleTeam: () =>
    getTeamBox = (appendTo, headline = "Mannschaft", id = @content.teamId) =>
      box = @box(3, appendTo).append($('<h4/>').text(headline))
      Fss.getTeam id, (team) ->
        box.append(
          $('<a/>')
          .attr('href', "/page/team-#{team.id}.html")
          .text("#{team.name} (#{team.state})")
          .attr('title', "#{team.short} (#{team.type}")
        ).append("<br/>ID: #{id}")
    @headline = "Mannschaft"
    switch @content.reason
      when "correction"
        @headline += " - Korrektur"
        @openType = (div) =>
          getTeamBox(div)
          @box(4, div)
            .append($('<h4/>').text("Korrektur"))
            .append("Name: #{@content.name}<br/>Kurz: #{@content.short}<br/>Typ: #{@content.type}")
          @getActionBox div, () =>
            Fss.post 'set-team-name', @content, (data) => @confirmDone()

      when "together"
        @headline += " - Zusammenführen"
        @openType = (div) =>
          action = (params = {}) =>
            params.newTeamId = @content.newTeamId
            params.teamId = @content.teamId
            Fss.post 'set-team-together', params, (data) => @confirmDone()
          getTeamBox(div)
          getTeamBox(div, "Richtige Mannschaft", @content.newTeamId)
          @getActionBox(div, () => action() )
          .append($('<button/>').text('Immer beheben').click( () => 
            @confirmAction(
              () -> action( always: true ),
              "Beim Import wird in Zukunft immer automatisch der Name ersetzt."
            )
          ))

      when "logo"
        @headline += " - Logo"
        @openType = (div) =>
          getTeamBox(div)
          for image in @content.attached_files
            @box(3, div)
            .append($('<img/>').attr('src', "/files/errors/#{image}").css('width', "200px"))
            .append($('<button/>').text('Auswählen').click () => 
              @confirmAction () =>
                Fss.post 'add-team-logo', teamId: @content.teamId, attachedFile: image, (data) => @confirmDone()
              
            )
          @getActionBox(div)

      when "other"
        @headline += " - Freitext"
        @openType = (div) =>
          getTeamBox(div)
          @box(5, div).append($('<pre/>').text(@content.description))
          @getActionBox(div)

  handleDate: () =>
    getDateBox = (appendTo, headline = "Termin", id = @content.dateId) =>
      box = @box(5, appendTo).append($('<h4/>').text(headline))
      Fss.post 'get-date', dateId: id, (data) ->
        date = data.date
        Fss.post 'get-place', placeId: date.place_id, (data) ->
          place = data.place
          Fss.post 'get-event', eventId: date.event_id, (data) ->
            event = data.event
            box.append(
              $('<a/>')
              .attr('href', "/page/date-#{date.id}.html")
              .text("#{date.name} (#{(new Date(date.date)).toLocaleDateString()})")
            )
            .append("<br/>ID: #{id}")
            .append("<br/>Ort: #{place.name}")
            .append("<br/>Typ: #{event.name}")
            .append("<br/>")
            .append($("<pre/>").text(date.description))
            .append("<br/>Disziplinen: #{date.disciplines}")

    @headline = "Termin"
    switch @content.reason
      when "change"
        @headline += " - Änderung"
        @openType = (div) =>
          getDateBox(div)
          correctBox = @box(5, div)
          .append($('<h4/>').text("Korrektur"))
          .append("Name: #{@content.date.name}")


          Fss.post 'get-place', placeId: @content.date.placeId, (data) =>
            place = data.place
            Fss.post 'get-event', eventId: @content.date.eventId, (data) =>
              event = data.event
              correctBox
              .append("<br/>Ort: #{place.name}")
              .append("<br/>Typ: #{event.name}")
              .append("<br/>")
              .append($("<pre/>").text(@content.date.description))
              for discipline, name of Fss.disciplines
                correctBox.append("<br/>#{name}: #{@content.date[discipline]}")
          @getActionBox(div, () => 
            data = @content.date
            data.dateId = @content.dateId
            Fss.post 'set-date', data, (d) => @confirmDone()
          , 2)

  formatDateTime: (dateTime) =>
    date = new Date(dateTime)
    date.toLocaleString()

$ ->
  reloadErrors()


