
disciplineRow = (title) ->
  title = Fss.disciplines[title] || "Einzeldisziplin"
  $('<tr/>').append($('<th/>').attr('colspan', 5).text(title).addClass("text-center"))

class Score
  constructor: (@discipline, @data, table) ->
    @move = false
    table.append(@row())
    @setMoveStatus()

  setMoveStatus: () =>
    if @move
      @ownTd.text("")
      @moveTd.text("X")
      @tr.removeClass("warning").addClass("danger")
    else
      @ownTd.text("X")
      @moveTd.text("")
      @tr.removeClass("danger").addClass("warning")

  competitionLink: () =>
    $('<a/>')
    .attr('href', "/page/competition-#{@data.competition_id}.html")
    .text("#{@data.name} - #{@data.place} - #{@data.event}")

  doMove: (teamId, callback) =>
    if @move
      postData = 
        discipline: @discipline
        scoreId: @data.id
        teamId: teamId
      if @discipline is 'single'
        Fss.post 'set-score-team', postData, callback
      else
        Fss.post 'set-group-score-team', postData, callback
    else
      callback()

  row: () =>
    @ownTd = $('<td/>')
    @moveTd = $('<td/>')
    @tr = $('<tr/>')
    .append(@ownTd)
    .append($('<td/>').text(@data.id))
    .append($('<td/>').text(@data.team_number))
    .append($('<td/>').append(@competitionLink()))
    .append($('<td/>').text(@data.date))
    .append(@moveTd)
    .click () => @toggleStatus()

  shortRow: () =>
    $('<tr/>')
    .append($('<td/>').text(@data.id))
    .append($('<td/>').append(@competitionLink()))
    .append($('<td/>').text(@data.date))

  toggleStatus: () =>
    @move = !@move
    @setMoveStatus()

$ () ->
  Fss.getTeams null, (teams) ->
    for team in teams
      $('#team-from').append($('<option/>').text(team.display).val(team.value))
      $('#team-move').append($('<option/>').text(team.display).val(team.value))


  scores = []
  scoreTable = $('#score-table')
  $('#team-from').change () ->
    scoreTable.find('tr').not($('#headline')).remove()
    teamId = $(this).val()
    Fss.post 'get-scores-for-team', teamId: teamId, (result) ->      
      scores = []
      for discipline in ['la', 'fs', 'gs', 'single']
        continue unless result[discipline].length > 0
        scoreTable.append(disciplineRow(discipline))
        scoreTable.append($('#headline').clone().attr('id', null))
        for score in result[discipline]
          scores.push(new Score(discipline, score, scoreTable))

  $('#use-team-from').click () ->
    $('#team-move').val($('#team-from').val())

  $('#do-move').click () ->
    count = 0
    for score in scores
      count++ if score.move 
    return new WarningFssWindow("Keine Zeiten ausgewählt.") if count is 0

    Fss.getTeam $('#team-from').val(), (teamFrom) ->
      Fss.getTeam $('#team-move').val(), (teamTo) ->
        infoTable = $('<table/>').addClass('table')
        .append(
          $('<tr/>')
          .append($('<th/>').text('Von'))
          .append($('<td/>').text(teamFrom.id))
          .append($('<td/>').text(teamFrom.name))
          .append($('<td/>').text(teamFrom["short"]))
          .append($('<td/>').text(teamFrom.state))
        )
        .append(
          $('<tr/>')
          .append($('<th/>').text('Nach'))
          .append($('<td/>').text(teamTo.id))
          .append($('<td/>').text(teamTo.name))
          .append($('<td/>').text(teamTo["short"]))
          .append($('<td/>').text(teamTo.state))
        )

        relevantScoresTable = $('<table/>').addClass('table')
        for score in scores
          continue unless score.move
          relevantScoresTable.append(score.shortRow())

        (new FssWindow('Wirklich Zeiten verschieben?'))
        .add(new FssFormRow(infoTable))
        .add(new FssFormRow(relevantScoresTable))
        .on('submit', () ->
          currentIndex = 0

          moveIt = () ->
            scores[currentIndex].doMove teamTo.id, () ->
              currentIndex++
              if currentIndex < scores.length
                moveIt()
              else
                new AlertFssWindow "Zeiten verschoben", "Die #{count} Zeiten wurden verschoben.", () ->
                  location.reload()
          moveIt()
        ).open()
    false
    