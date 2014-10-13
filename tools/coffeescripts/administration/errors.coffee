$ ->
  $('.add-hint').click () ->
    competitionId = $(@).data('competition-id')
    description = $(@).data('description')
    FssWindow.build('Hinweis hinzufügen')
      .add(new FssFormRowTextarea('hint', 'Hinweis', description))
      .on('submit', (data) ->
        data.competitionId = competitionId
        Fss.postReload('add-hint', data)
      )
      .open()
  $('.delete-hint').click () ->
    competitionHintId = $(@).data('competition-hint-id')
    new ConfirmFssWindow 'Hinweis löschen', 'Wirklich löschen?', () ->
      data =
        competitionHintId: competitionHintId
      Fss.postReload('delete-hint', data)