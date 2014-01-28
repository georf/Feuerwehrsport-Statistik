#= require FssWindow
#= require FssFormRow

class AlertFssWindow extends FssWindow
  constructor: (title, message) ->
    super(title)
    @add(new FssFormRow($('<p/>').text(message)))
    @add(new FssFormRow($('<button/>').text('OK').on('click', (e) =>
      e.preventDefault()
      @close()
    )))
    @open()