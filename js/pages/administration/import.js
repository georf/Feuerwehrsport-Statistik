$(function() {

  var globalInputLines = [];
  var singleFields = ['name', 'firstname', 'team', 'run'];
  var multipleFields = ['time', 'col'];

  var InputLineField = function(line, name) {
    var self = this;
    var container;
    var selectField;

    var constructor = function() {

      container = $('<span/>').addClass('input-line-field');
      selectField = $('<select/>');

      var f;

      for (var i = 0; i < singleFields.length; i++) {
        f = singleFields[i];
        selectField.append($('<option/>').text(f).val(f));
      };
      for (var i = 0; i < multipleFields.length; i++) {
        f = multipleFields[i];
        selectField.append($('<option/>').text(f).val(f));
      };

      var removeButton = $('<button/>').text('x').click(function() {
        line.removeField(self);
      });
      container.append(selectField).append(removeButton);

      self.select(name);
    };

    this.getHtml = function() {
      return container;
    };

    this.select = function (name) {
      selectField.find("option[value='" + name + "']").attr('selected', true);
    };

    this.remove = function () {
      container.remove();
    };

    this.val = function () {
      return selectField.find("option:selected").val();
    };

    constructor();
  };

  var InputLine = function (discipline) {
    var self = this;

    var fields = [];

    var constructor = function () {
      globalInputLines.push(self);

      var start = [];
      if ($.inArray(discipline, ['hl', 'hb']) != -1) {
        start = ['name', 'firstname', 'team', 'time', 'time'];
      } else if (discipline == 'la') {
        start = ['team', 'time', 'time'];
      } else if (discipline == 'gs') {
        start = ['team', 'time'];
      } else if (discipline == 'fs') {
        start = ['team', 'time', 'run'];
      }

      for (var i = 0; i < start.length; i++) {
        fields.push(new InputLineField(self, start[i]));
      };
    };

    this.getHtml = function () {
      var container = $('<div/>').addClass('input-line');
      for (var i = 0; i < fields.length; i++) {
        container.append(fields[i].getHtml());
      };
      var addButton = $('<button/>').text('+').click(function() {
        var newField = new InputLineField(self, 'time');
        fields.push(newField);
        newField.getHtml().insertBefore(addButton);
      });
      container.append(addButton);
      return container;
    };

    this.removeField = function (field) {
      for (var i = 0; i < fields.length; i++) {
        if (fields[i] == field) {
          field.remove();
          fields.splice(i, 1);
          return;
        }
      };
    };

    this.val = function () {
      var outputs = [];
      for (var i = 0; i < fields.length; i++) {
        outputs.push(fields[i].val());
      };
      return outputs.join(',');
    }

    constructor();
  };


  var TestScoreResult = function (raw, fields) {
    var self = this;
    var $tr = $('<tr/>');

    var td = function (text) {
      var $td = $('<td/>').text(text);
      $tr.append($td);
      return $td;
    };

    var needField = function(field) {
      fields[field] = true;
    };

    var needTimes = function(count) {
      fields['times'] = Math.max(fields['times'], count);
    };

    var constructor = function() {
      if (raw.name != undefined)        needField('name');
      if (raw.firstname != undefined)   needField('firstname');
      if (raw.team != undefined)        needField('team');
      if (raw.team_id != undefined)     needField('team_id');
      if (raw.team_number != undefined) needField('team_number');
      if (raw.run != undefined)         needField('run');
      if (raw.times != undefined)       needTimes(raw['times'].length);
    };

    var buildTr = function(fields) {
      $tr.click(function() {
        $tr.toggleClass('correct').toggleClass('not-correct');
        raw.correct = !raw.correct;
      });

      if (raw.correct) $tr.addClass('correct');
      else $tr.addClass('not-correct');

      var $td;

      if (raw.name != undefined) {
        $td = td(raw['name']);
        if (!raw['found_person']) $td.addClass('person-not-found');
      } else if (fields.name) {
        td('');
      }

      if (raw.firstname != undefined) {
        $td = td(raw['firstname']);
        if (!raw['found_person']) $td.addClass('person-not-found');
      } else if (fields.firstname) {
        td('');
      }

      if (raw.team != undefined) {
        td(raw['team']);
      } else if (fields.team) {
        td('');
      }

      if (raw.team_id != undefined) {
        td(raw['team_id']);
      } else if (fields.team_id) {
        td('');
      }

      if (raw.team_number != undefined) {
        td(raw['team_number']);
      } else if (fields.team_number) {
        td('');
      }

      if (raw.run != undefined) {
        td(raw['run']);
      } else if (fields.run) {
        td('');
      }

      if (raw.times != undefined) {
        for (var i = 0; i < raw['times'].length; i++) {
          $td = td(raw['times'][i]);
          if (raw['times'][i] == 'NULL') $td.addClass('null');
        }
        for (var z = raw['times'].length; z < fields.times; z++) td('')
      } else {
        for (var z = 0; z < fields.times; z++) td('')
      }

      td(raw['line']).addClass('raw-line');

      return $tr;
    }

    this.getFields = function () {
      return fields;
    };

    this.getHtml = function (fields) {
      return buildTr(fields);
    };

    this.isCorrect = function () {
      return raw.correct;
    };

    this.getObject = function () {
      return raw;
    };

    constructor();
  };


  var MissingTeam = function (name, callback) {
    this.getHtml = function () {
      return $('<li/>').text(name).click(function () {
        checkLogin(function() {
          name = name.replace(/ II?$/, '');
          var longname = name;

          var options = [
            { value: 'Team', display: 'Zusammenschluss (Team)'},
            { value: 'Feuerwehr', display: 'Einzelne Feuerwehr'}
          ];

          if (name.match(/^FF/)) {
            name = name.replace(/^FF\s+/, '');
          } else {
            longname = 'FF '+name;
          }

          FormWindow.create([
            ['Text', 'name', 'Name', longname, 'Vollständiger Name'],
            ['Text', 'short', 'Abkürzung', name, 'Kurzer Name (maximal 10 Zeichen)'],
            ['Select', 'type', 'Typ der Mannschaft', options[1].value, null, {options: options}]
          ], 'Mannschaft anlegen', '')
          .open().submit(function(data) {
            this.close();

            wPost('add-team', data, function() {
              callback();
            });
          });
        });
      });
    }
  };


  var Discipline = function (discipline, sex) {
    var self = this;

    var $discipline = $('#disciplines');
    var name = window.disciplines[discipline];
    var $fieldset;
    var $textarea;
    var $selectSeparator;

    var inputLine;
    var $testScoresContainer = $('<div/>');

    var constructor = function () {
      $fieldset = $('<fieldset/>')
        .addClass('discipline')
        .addClass('discipline-' + discipline)
        .addClass(sex);
      var $legend = $('<legend/>').text(name + " - " + window.sexes[sex]);
      var $content = $('<div/>');
      var $removeButton = $('<button/>').addClass('top-right').text('Löschen').click(remove);

      $content.append($removeButton);

      $legend.click(function() {
        $content.toggle();
      });

      inputLine = new InputLine(discipline);
      $content.append(inputLine.getHtml());
      $fieldset.append($legend).append($content);

      $content = $('<div/>');
      $textarea = $('<textarea/>');
      $selectSeparator = $('<select/>')
        .append($('<option/>').text('TAB').val("\t"))
        .append($('<option/>').text(',').val(","));
      var $testButton = $('<button/>').text('Testen').click(testInput);
      $content.append($textarea).append($selectSeparator).append($testButton);
      $fieldset.append($content).append($testScoresContainer);

      $discipline.append($fieldset);

      disciplines.push(self);
    };

    var testInput = function () {
      wPost('get-test-scores', {
        discipline: discipline,
        sex: sex,
        raw_scores: $textarea.val(),
        seperator: $selectSeparator.find('option:selected').val(),
        headlines: inputLine.val()
      }, function(data) {
        if (data.success) {
          $textarea.animate({ height: 90 });
          $testScoresContainer.children().remove();
          showMissingTeams(data.teams);
          showTestScores(data.scores);
        } else {
          alert(data.message);
        }
      });
    };

    var resultScores;
    var showTestScores = function (scores) {
      console.log(scores);

      resultScores = [];

      var $table = $('<table/>');
      var testScoreResult;
      var fields = { times: 0 };

      for (var i = 0; i < scores.length; i++) {
        testScoreResult = new TestScoreResult(scores[i], fields);
        fields = testScoreResult.getFields();
        resultScores.push(testScoreResult);
      };
      for (var z = 0; z < resultScores.length; z++) {
        $table.append(resultScores[z].getHtml(fields));
      };
      var $button = $('<button/>').text('Eintragen').click(addResultScores);
      $testScoresContainer.append($table).append($button);
    };

    var addResultScores = function () {
      var scores = [];
      for (var i = 0; i < resultScores.length; i++) {
        if (resultScores[i].isCorrect()) {
          scores.push(resultScores[i].getObject());
        }
      };
      var input = {
        scores: scores,
        competition_id: $('#competitions option:selected').val(), 
        discipline: discipline,
        sex: sex
      };
      wPost('add-scores', input, function(data) {
        if (data.success) {
          loadScores();
          remove();
        } else {
          alert(data.message);
        }
      });
    };

    var showMissingTeams = function (teams) {
      console.log(teams);
      if (!teams.length) return;

      var $ul = $('<ul/>').addClass('disc').addClass('missing-teams');
      var missingTeam;
      for (var i = 0; i < teams.length; i++) {
        missingTeam = new MissingTeam(teams[i], testInput);
        $ul.append(missingTeam.getHtml());
      };
      $testScoresContainer.append($ul);
    };

    var remove = function () {
      for (var i = 0; i < disciplines.length; i++) {
        if (disciplines[i] == self) {
          disciplines.splice(i, 1);
        }
        $fieldset.remove();
      };
    };

    constructor();
  };


  var changeCompetitionLink = function () {
    var $option = $selectCompetition.find('option:selected');
    if ($option.length) {
      $('#competition-link')
        .attr('href', '/page/competition-' + $option.val() + '.html')
        .text($option.text());
    }
    loadScores();
  };
  var $selectCompetition = $('#competitions').change(changeCompetitionLink);

  var competitions = [];
  var disciplines = [];

  var lastValue = null;
  var selectCompetitionType = function () {
    var value = $("input[name='competition-type']:checked").val();
    if (lastValue == value) return;
    lastValue = value;

    $('#select-competitions').show();
    $('#create-competitions').hide();
    $('#competition-scores').show();

    var c = "";
    var select = $('#competitions');
    select.children().remove();

    var sortedCompetitions = competitions.slice();

    if (value == 'sorted') {
      sortedCompetitions.reverse();
    } else if (value == 'latest') {
      sortedCompetitions.sort(function (a, b) {
        return b.id - a.id;
      });
    } else {
      $('#select-competitions').hide();
      $('#create-competitions').show();
      $('#competition-scores').hide();
    }
    for (var i = 0; i < sortedCompetitions.length; i++) {
      c = sortedCompetitions[i];
      select.append($('<option value="' + c.id + '">' + c.date + ' - ' + c.event + ' - ' + c.place + '</option>'));
    };
    changeCompetitionLink();
  };

  var loadScores = function () {
    wPost('get-competition-scores', { competition_id: $selectCompetition.find('option:selected').val()}, function (data) {
      var $container = $('#competition-scores');
      $container.children().remove();

      if (data.success) {
        var $table = $('<table/>');
        for (var key in data.scores) {
          for (var sex in data.scores[key]) {
            if (data.scores[key][sex] > 0) {
              $table.append($('<tr/>')
                .addClass('discipline-'+key).addClass('discipline').addClass(sex)
                .append($('<th/>').text(key + '-' + sex))
                .append($('<td/>').text(data.scores[key][sex]))
              );
            }
          }
        }
        $container.append($table);
      }
    });
  };

  var reloadCompetitions = function (callback) {
    getCompetitions(function(newCompetitions) {
      competitions = newCompetitions;
      callback();
    });
  };

  $("input[name='competition-type']").change(selectCompetitionType);

  $(".add-place").click(function () {
    checkLogin(function() {
      var w = new FormWindow([
          ['Text', 'name', 'Name', '']
      ], 'Ort hinzufügen');
      w.submit(function(data) {
        w.close();
        wPost('add-place', data, function(data) {
          if (data.success) {
            FormWindow.alert('Eingetragen');
            reloadCompetitions(selectCompetitionType);
          } else {
            FormWindow.warning('Etwas ging schief', JSON.stringify(data));
          }
        });
      }).open();
    });
  });

  $(".add-event").click(function () {
    checkLogin(function() {
      var w = new FormWindow([
          ['Text', 'name', 'Name', '']
      ], 'Typ hinzufügen');
      w.submit(function(data) {
        w.close();
        wPost('add-event', data, function(data) {
          if (data.success) {
            FormWindow.alert('Eingetragen');
            reloadCompetitions(selectCompetitionType);
          } else {
            FormWindow.warning('Etwas ging schief', JSON.stringify(data));
          }
        });
      }).open();
    });
  });

  $(".add-competition").click(function () {
    checkLogin(function() {
      getEvents(function (events) {
        getPlaces(function (places) {
          var eventOptions = [];
          for (var i = 0; i < events.length; i++) {
            eventOptions.push({display: events[i].name, value: events[i].id});
          }
          var placeOptions = [];
          for (var i = 0; i < places.length; i++) {
            placeOptions.push({display: places[i].name, value: places[i].id});
          }
          var w = new FormWindow([
              ['Text', 'name', 'Name', ''],
              ['Select', 'place_id', 'Ort', null, null, {options: placeOptions}],
              ['Select', 'event_id', 'Ort', null, null, {options: eventOptions}],
              ['Date', 'date', 'Datum', '']
          ], 'Wettkampf hinzufügen');
          w.submit(function(data) {
            w.close();
            wPost('add-competition', data, function(data) {
              if (data.success) {
                FormWindow.alert('Eingetragen');
                reloadCompetitions(selectCompetitionType);
              } else {
                FormWindow.warning('Etwas ging schief', JSON.stringify(data));
              }
            });
          }).open();
        });
      });
    });
  });

  $('.add-discipline').click(function() {
    var classes = this.className.split(' ');
    for (var i = classes.length - 1; i >= 0; i--) {
      var res = classes[i].match(/^discipline-([a-z]{2})-((?:fe)?male)$/);
      if (res) {
        new Discipline(res[1], res[2]);
        return false;
      }
    };
  });

  reloadCompetitions(selectCompetitionType);
});
