#= extern EventHandler
#= require InputLine
#= require TestScoreResult
#= require MissingTeam

class Discipline extends EventHandler
  constructor: (@discipline, @sex) ->
    super
    @testScoresContainer = $('<div/>')
    
    @fieldset = $('<fieldset/>')
      .addClass('discipline')
      .addClass('discipline-' + @discipline)
      .addClass(@sex)

    content = $('<div/>')
    $('<legend/>')
      .text(Fss.disciplines[@discipline] + " - " + Fss.sexes[@sex])
      .click( () -> content.toggle() )
      .appendTo(@fieldset)

    $('<button/>')
      .addClass('top-right')
      .text('Löschen')
      .click(@remove)
      .appendTo(content)

    @inputLine = new InputLine(@discipline)
    content.append(@inputLine.get())
    @fieldset.append(content)

    @textarea = $('<textarea/>')
    @selectSeparator = $('<select/>')
      .append($('<option/>').text('TAB').val("\t"))
      .append($('<option/>').text(',').val(","))

    content = $('<div/>')
      .append(@textarea)
      .append(@selectSeparator)
      .append($('<button/>').text('Testen').click(@testInput))
    @fieldset.append(content).append(@testScoresContainer)

    $('#disciplines').append(@fieldset)

  testInput: () =>
    Fss.post 'get-test-scores', 
      discipline: @discipline,
      sex: @sex,
      rawScores: @textarea.val(),
      seperator: @selectSeparator.val(),
      headlines: @inputLine.val()
    , (data) =>
      @textarea.animate(height: 90)
      @testScoresContainer.children().remove()
      @showMissingTeams(data.teams)
      @showTestScores(data.scores)
  
  showTestScores: (scores) =>
    @resultScores = []
    table = $('<table/>').addClass('table table-bordered table-condensed')
    fields = { times: 0 }

    for score in scores
      testScoreResult = new TestScoreResult(score, fields)
      fields = testScoreResult.getFields()
      @resultScores.push(testScoreResult)
    
    for resultScore in @resultScores
      table.append(resultScore.get(fields))
    button = $('<button/>').text('Eintragen').click(@addResultScores)
    @testScoresContainer.append(table).append(button)

  addResultScores: () =>
    scores = []
    for resultScore in @resultScores
      scores.push(resultScore.getObject()) if resultScore.isCorrect()

    input =
      scores: scores
      competitionId: $('#competitions').val()
      discipline: @discipline
      sex: @sex
    Fss.post 'add-scores', input, (data) =>
      @fire('refresh-results')
      @remove()

  showMissingTeams: (teams) =>
    return unless teams.length 
    ul = $('<ul/>').addClass('disc').addClass('missing-teams')
    for team in teams
      missingTeam = new MissingTeam(team, @testInput)
      ul.append(missingTeam.get())
    @testScoresContainer.append(ul)

  remove: () =>
    @fieldset.remove()
