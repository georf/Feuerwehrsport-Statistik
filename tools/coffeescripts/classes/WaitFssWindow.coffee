#= require FssWindow
#= require FssFormRow

class WaitFssWindow extends FssWindow
  constructor: () ->
    super("Bitte warten")
    @add(new FssFormRow($('<div/>').addClass("wait-fss-window")))
    @open()