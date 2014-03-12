new SortTable(selector: '.scores-hb, .scores-hl, .scores-zk', sortCol: 2, noSorting: 'last')
new SortTable(selector: '.scores-hb-final, .scores-hl-final', sortCol: 2, noSorting: 3)
new SortTable(selector: '.group-scores', sortCol: 1)

$('#add-file').click () ->
  Fss.checkLogin () ->
    $('#add-file-form').show()
    $('#add-file').hide()

fileCounter = 0
$('#more-files').click (ev) ->
  ev.preventDefault()
  fileCounter++
  tr = $('.input-file-row').closest('tr').clone().removeClass('input-file-row')
  file = tr.find('input[type=file]').val('')
  file.attr('name', file.attr('name').replace(/[0-9]+/,'') + fileCounter)
  tr.find(':checkbox').each () ->
    checkbox = $(this).removeAttr('checked')
    checkbox.attr('name', checkbox.attr('name').replace(/[0-9]+/,'') + fileCounter)
  $('.input-file-row').closest('table').append(tr)


$('#report-error').click (ev) ->
  ev.preventDefault()
  competitionId = $(this).data('competition-id')
  competitionName = $(this).data('competition-name')

  Fss.checkLogin () ->
    options = [
      { value: 'name', display: 'Name des Wettkampfs vorschlagen'},
      { value: 'hint', display: 'Hinweis geben'}
    ]
    FssWindow.build('Auswahl des Fehlers')
    .add(new FssFormRowDescription('Bitte wählen Sie den Typ der Meldung aus:'))
    .add(new FssFormRowRadio('what', 'Was wollen Sie tun?', null, options))
    .on('submit', (data) ->
      selected = data.what

      if selected is 'name'
        FssWindow.build('Namen vorschlagen')
        .add(new FssFormRowDescription('Bitte geben Sie den Namen an:'))
        .add(new FssFormRowText('name', 'Name', competitionName))
        .on('submit', (data) ->
          data.reason = selected
          data.type = 'competition'
          data.competitionId = competitionId
          Fss.addError(data)
        )
        .open()
      else if selected is 'hint'
        FssWindow.build('Hinweis beschreiben')
        .add(new FssFormRowDescription('Bitte geben Sie ihren Hinweis ausführlich an:'))
        .add(new FssFormRowTextarea('description', 'Beschreibung', ''))
        .on('submit', (data) ->
          data.reason = selected
          data.type = 'competition'
          data.competitionId = competitionId
          Fss.addError(data)
        )
        .open()
    )
    .open()
