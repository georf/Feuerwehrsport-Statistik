class TestScoreResult
  constructor: (@raw, @fields) ->
    @needField('name')               if @raw.name?
    @needField('firstname')          if @raw.firstname? 
    @needField('team')               if @raw.team?      
    @needField('team_id')            if @raw.team_id?   
    @needField('team_number')        if @raw.team_number?
    @needField('run')                if @raw.run?       
    @needTimes(@raw['times'].length) if @raw.times?

  needField: (field) =>
    @fields[field] = true
  
  needTimes: (count) => 
    @fields['times'] = Math.max(@fields['times'], count)

  getFields: () =>
    @fields

  get: (fields) =>
    tr = $('<tr/>').click () =>
      tr.toggleClass('correct').toggleClass('not-correct')
      @raw.correct = !@raw.correct
    
    appendTd = (text) -> $('<td/>').text(text).appendTo(tr)

    if @raw.correct
      tr.addClass('correct')
    else
      tr.addClass('not-correct');

    if @raw.name?
      td = appendTd(@raw['name'])
      td.addClass('person-not-found') unless @raw['found_person'] 
    else if fields.name
      appendTd('')

    if @raw.firstname?
      td = appendTd(@raw['firstname'])
      td.addClass('person-not-found') unless @raw['found_person'] 
    else if fields.firstname
      appendTd('')

    if @raw.team?
      appendTd(@raw['team'])
    else if fields.team
      appendTd('')

    if @raw.team_id?
      appendTd(@raw['team_id'])
    else if fields.team_id
      appendTd('')

    if @raw.team_number?
      appendTd(@raw['team_number'])
    else if fields.team_number
      appendTd('')

    if @raw.run?
      appendTd(@raw['run'])
    else if fields.run
      appendTd('')

    if @raw.times?
      for time in @raw.times
        td = appendTd(time)
        td.addClass('null') if time is 'NULL'
      for i in [@raw.times.length..fields.times]
        appendTd('')
    else
      for i in [0..fields.times]
        appendTd('')
    appendTd(@raw['line']).addClass('raw-line')
    tr

  isCorrect: () =>
    @raw.correct

  getObject: () =>
    @raw