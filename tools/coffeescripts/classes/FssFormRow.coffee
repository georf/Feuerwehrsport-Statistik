#= EventHandler

class FssFormRow extends EventHandler
  constructor: (@fields...)->
    super
  
  focus: () -> false

  appendData: (data) => data

  content: () =>
    container = $('<div/>').addClass('fss-form-row')
    container.append(field) for field in @fields
    container
