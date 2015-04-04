#= require AlertFssWindow
#= require BigImage
#= require ConfirmFssWindow
#= require EventHandler
#= require Fss
#= require FssFormRow
#= require FssWindow
#= require SortTable
#= require WaitFssWindow
#= require WarningFssWindow

$ ()->
  $('.nav-tabs li a').click (e)->
    e.preventDefault()
    $(this).tab('show')

  $('img.big').each ->
    new BigImage($(this))

  $('#select-state').click () ->
    element = $(this)
    for_id = element.data('for-id')
    for_type = element.data('for-type')
    current = element.data('current')
    states = [
      {value:'NULL',display:'unbekannt'},
      {value:'BW',  display:'Baden-Württemberg'},
      {value:'BY',  display:'Bayern'},
      {value:'BE',  display:'Berlin'},
      {value:'BB',  display:'Brandenburg'},
      {value:'HB',  display:'Bremen'},
      {value:'HH',  display:'Hamburg'},
      {value:'HE',  display:'Hessen'},
      {value:'MV',  display:'Mecklenburg-Vorpommern'},
      {value:'NI',  display:'Niedersachsen'},
      {value:'NW',  display:'Nordrhein-Westfalen'},
      {value:'RP',  display:'Rheinland-Pfalz'},
      {value:'SL',  display:'Saarland'},
      {value:'SN',  display:'Sachsen'},
      {value:'ST',  display:'Sachsen-Anhalt'},
      {value:'SH',  display:'Schleswig-Holstein'},
      {value:'TH',  display:'Thüringen'},
      {value:'CZ',  display:'Tschechien'},
      {value:'DE',  display:'Deutschland'},
      {value:'AT',  display:'Österreich'},
      {value:'PL',  display:'Polen'}
    ]

    Fss.checkLogin () ->
      FssWindow.build('Bundesland auswählen')
      .add(new FssFormRowSelect('state', 'Land', current, states))
      .on('submit', (data) ->
        data['id'] = for_id
        data['for'] = for_type
        Fss.postReload 'set-state', data
      )
      .open()

  $('#add-link').click () ->
    element = $(this)
    for_id = element.data('for-id')
    for_table = element.data('for-table')

    Fss.checkLogin () ->
      FssWindow.build('Link hinzufügen')
      .add(new FssFormRowText('name', 'Name'))
      .add(new FssFormRowDescription('Beschreibung des Links'))
      .add(new FssFormRowText('url', 'Link', 'http://'))
      .on('submit', (data) ->
        data.url = data.url.replace(/^http:\/\/(https?:\/\/)/, "$1")
        data.url = "http://#{data.url}" unless data.url.match(/^https?:\/\//)
        data['id'] = for_id
        data['for'] = for_table
        Fss.postReload 'add-link', data
      )
      .open()

  Fss.tdScoreHandle '.group-scores td.person', (button, score, table) ->
    button
    .addClass('edit-team-group')
    .attr('title', 'Position bearbeiten')
    .click () ->
      for className in table.attr('class').split(/\s+/)
        result = className.match(/^scores-((la)|(fs)|(gs))$/)
        Fss.teamMates(result[1], score) if result

  Fss.tdScoreHandle '.single-scores td.number', (button, score) ->
    button
    .addClass('edit-team-group')
    .attr('title', 'Mannschaftwertung ändern')
    .click () ->
      Fss.checkLogin () ->
        Fss.post 'get-score-information', {scoreId: score, discipline: 'zk'}, (scoreData) ->
          personId = scoreData.score.person_id
          Fss.getPerson personId, (person) ->
            numbers = [
              { display: 'Außer der Wertung', value: -6 },
              { display: 'Achtelfinale', value: -5 },
              { display: 'Viertelfinale', value: -4 },
              { display: 'Halbfinale', value: -3 },
              { display: 'Finale', value: -2 },
              { display: 'Einzelstarter', value: -1 },
              { display: 'Mannschaft 1', value: 0 },
              { display: 'Mannschaft 2', value: 1 },
              { display: 'Mannschaft 3', value: 2 },
              { display: 'Mannschaft 4', value: 3 },
              { display: 'Mannschaft 5', value: 4 }
            ]
            w = FssWindow.build('Wertungszeit zuordnen')
              .add(new FssFormRowDescription("Sie ordnen der Person <strong>#{person.firstname} #{person.name}</strong> bei diesem Wettkampf einer Wertung zu."))
            for score in scoreData.scores
              w.add(new FssFormRowSelect(
                "score#{score.id}", 
                "#{score.discipline}: #{score.timeHuman}",
                score.team_number,
                numbers
              ))
            w.on('submit', (data) ->
              Fss.reloadOnArrayReady scoreData.scores, 'set-score-number', (score) ->
                scoreId: score.id
                teamNumber: data["score#{score.id}"]
            )
            .open()

  Fss.tdScoreHandle '.single-scores td.team', (button, score, table, td) ->
    if td.text() is ''
      button
        .addClass('edit-team-group-new')
        .attr('title', 'Mannschaft zur Zeit zuordnen')
    else
      button
        .addClass('edit-team-group')
        .attr('title', 'Mannschaft ändern')
    button
      .appendTo(td)
      .click () ->
        Fss.checkLogin () ->
          Fss.post 'get-score-information', {scoreId: score, discipline: 'zk'}, (scoreData) ->
            personId = scoreData.score.person_id
            Fss.getPerson personId, (person) ->
              Fss.getTeams personId, (teams) ->
                w = FssWindow.build('Wertungszeit zuordnen')
                .add(new FssFormRowDescription("Sie ordnen der Person <strong>#{person.firstname} #{person.name}</strong> bei diesem Wettkampf einer Mannschaft zu."))
                teams.splice(0,0, {value: 'NULL', display: ''})
                w.add(new FssFormRowSelect('teamId', 'Mannschaft für die gestartet wurde: ', scoreData.score.team_id, teams))
                scoreTexts = []
                for score in scoreData.scores
                  text = score.discipline
                  text += ' Finale' if score.team_number is -2
                  text += ' Einzelstarter' if score.team_number is -1
                  text += ': ' + score.timeHuman
                  scoreTexts.push(text)
                w.add(new FssFormRowDescription("Folgende Zeiten sind davon betroffen:<br/>#{scoreTexts.join("<br/>")}"))
                .on('submit', (data) ->
                  Fss.reloadOnArrayReady scoreData.scores, 'set-score-team', (score) ->
                    data.scoreId = score.id
                    data
                )
                .open()