$ ->
  disciplines = []

  selectCompetition = $("select[name='competition']")
  selectTeam = $("select[name='team']")
  selectTeamNumber = $("select[name='number']")
  selectSex = $("select[name='sex']")

  selectCompetition.change () ->
    competitionId = selectCompetition.val()
    sex = selectSex.val()
    Fss.post 'get-teams', {competitionId: competitionId, sex: sex}, (data) ->
      selectTeam.find('option').remove()
      for team in data.teams
        $('<option/>').text(team.display).val(team.value).appendTo(selectTeam)

      selectTeam.change()

  selectSex.change () ->
    selectCompetition.change()

    if selectSex.val() == 'female'
      disciplines = ['hb', 'hl', 'gs', 'la', 'fs']
      $('#gs').show()
    else
      disciplines = ['hb', 'hl', 'la', 'fs']
      hide($('#gs'))

  selectTeam.change () ->
    selectTeamNumber.change()

  selectTeamNumber.change () ->
    $.each disciplines, (i, discipline) ->
      Fss.post 'get-team-scores',
        competitionId: selectCompetition.val()
        teamId: selectTeam.val()
        discipline: discipline
        teamNumber: selectTeamNumber.val()
        sex: selectSex.val()
      , (data) ->
        if data.score
          data.score.time = 'D' if data.score.time is 99999999 || data.score.time is '99999999'
          $("input[name='#{discipline.toUpperCase()}-time']").val(data.score.time)
          $("input[name='#{discipline.toUpperCase()}-points']").val(data.score.points)
        else
          $("input[name='#{discipline.toUpperCase()}-time']").val('')
          $("input[name='#{discipline.toUpperCase()}-points']").val('')



  selectSex.change()

hide = (selector) ->
  selector.hide()
  selector.find('input').val('')