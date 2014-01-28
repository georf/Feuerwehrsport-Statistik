#= EventHandler
#= FssFormRow

class FssWindow extends EventHandler
  constructor: (@title) ->
    super
    @rows = []
    @rendered = false
    @on('pre-submit', () =>
      @fire('submit', @data())
    )

  render: () =>
    @container = $('<div/>').addClass('fss-window').append(
      $('<div/>').addClass('fss-window-title').text(@title)
    )
    @darkroom = $('<div/>').addClass('darkroom')

    if @handlers['submit']? and @handlers['submit'].length > 0
      submit = $('<a/>').addClass('class').text('OK').on('click', (e) => 
        e.preventDefault()
        @fire('pre-submit')
      )
      cancel = $('<button/>').text('Abbrechen').on('click', (e) => 
        e.preventDefault()
        @fire('cancel')
      )
      @add(new FssFormRow(submit, cancel))

    form = $('<form/>').on('submit', (e) => 
      e.preventDefault()
      @fire('pre-submit')
    )
    form.append(row.content()) for row in @rows
    @container.append(form)
    @rendered = true
    @

  add: (row) =>
    @rows.push(row)

  open: () =>
    @render() unless @rendered

    $('body').append(@darkroom).append(@container)

    left = (window.innerWidth/2 - parseInt(@container.css('width'))/2)
    top = (window.innerHeight/2 - parseInt(@container.css('height'))/2)

    left = 10 if left < 10
    top = 10  if top < 10

    top += parseInt($(document).scrollTop())
    @container.css('top', top).css('left', left)

    for row in @rows
      break if row.focus() 
    @

  close: () =>
    @container.remove()
    @darkroom.remove()

  data: () =>
    data = {}
    data = row.appendData(data) for row in @row
    data