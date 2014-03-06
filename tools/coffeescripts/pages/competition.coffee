new SortTable(selector: '.scores-hb, .scores-hl, .scores-zk', sortCol: 2, noSorting: 5)
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
