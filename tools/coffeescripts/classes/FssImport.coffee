#= require Discipline

class FssImport
  constructor: () ->
    @selectCompetition = $('#competitions').change(@changeCompetitionLink)
    @competitions = []
    @lastValue = null

    $("input[name='competition-type']").change(@selectCompetitionType)

    $(".add-place").click () =>
      Fss.checkLogin () =>
        FssWindow.build("Ort hinzufügen")
        .add(new FssFormRowText('name', 'Name'))
        .on('submit', (data) => Fss.post 'add-place', data, @addSuccess)
        .open()

    $(".add-event").click () =>
      Fss.checkLogin () =>
        FssWindow.build("Typ hinzufügen")
        .add(new FssFormRowText('name', 'Name'))
        .on('submit', (data) => Fss.post 'add-event', data, @addSuccess)
        .open()

    $(".add-competition").click () =>
      Fss.checkLogin () =>
        Fss.getEvents (events) =>
          Fss.getPlaces (places) =>
            eventOptions = []
            for event in events
              eventOptions.push({display: event.name, value: event.id})
            
            placeOptions = []
            for place in places
              placeOptions.push({display: place.name, value: place.id})
            
            FssWindow.build("Wettkampf hinzufügen")
            .add(new FssFormRowText('name', 'Name'))
            .add(new FssFormRowSelect('placeId', 'Ort', null, placeOptions))
            .add(new FssFormRowSelect('eventId', 'Typ', null, eventOptions))
            .add(new FssFormRowDate('date', 'Datum'))
            .on('submit', (data) => Fss.post 'add-competition', data, @addSuccess)
            .open()

    $('.add-discipline').click (ev) =>
      for className in ev.target.className.split(' ')
        res = className.match(/^discipline-([a-z]{2})-((?:fe)?male)$/)
        if res
          discipline = new Discipline(res[1], res[2])
          discipline.on('refresh-results', () => @loadScores() )
          return false

    @reloadCompetitions(@selectCompetitionType)

  addSuccess: () =>
    @reloadCompetitions(@selectCompetitionType)
    new AlertFssWindow('Eingetragen')

  changeCompetitionLink: () =>
    option = @selectCompetition.find('option:selected')
    if option.length
      $('#competition-link')
        .attr('href', "/page/competition-#{option.val()}.html")
        .text(option.text())
    @loadScores()

  selectCompetitionType: () =>
    value = $("input[name='competition-type']:checked").val()
    return if @lastValue is value
    @lastValue = value

    $('#select-competitions').show()
    $('#create-competitions').hide()
    $('#competition-scores').show()

    select = $('#competitions')
    select.children().remove()

    sortedCompetitions = @competitions.slice()

    if value is 'sorted'
      sortedCompetitions.reverse()
    else if value is 'latest'
      sortedCompetitions.sort (a, b) -> return b.id - a.id
    else
      $('#select-competitions').hide()
      $('#create-competitions').show()
      $('#competition-scores').hide()
    
    for c in sortedCompetitions
      select.append($('<option/>').val(c.id).text("#{c.date} - #{c.event} - #{c.place}"))
    @changeCompetitionLink()

  loadScores: () =>
    Fss.post 'get-competition-scores', { competitionId: @selectCompetition.val() }, (data) =>
      container = $('#competition-scores')
      container.children().remove()

      table = $('<table/>').appendTo(container)
      for key, sexes of data.scores
        for sexName, sex of sexes
          if sex > 0
            table.append($('<tr/>')
              .addClass("discipline-#{key}").addClass('discipline').addClass(sexName)
              .append($('<th/>').text("#{key}-#{sexName}"))
              .append($('<td/>').text(sex))
            )

  reloadCompetitions: (callback) =>
    Fss.getCompetitions (newCompetitions) =>
      @competitions = newCompetitions
      callback()