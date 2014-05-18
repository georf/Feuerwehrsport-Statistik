# $ () ->
#   sex = null
#   count = 1

#   $('#sex, #sex2, #count').change () ->
#     sex = $("input[name='sex']:checked").val()
#     $('#button-box').show() if sex != 'male' and sex != 'female'
#     count = parseInt($('#count').val())

#   $('#button-box').hide().click () ->
#     $('#form-box').hide()

#     Fss.checkLogin () ->
#       Fss.post 'get-persons', {sex: sex}, (data) ->
#         new NewRow(count, data.persons);

# class NewRow
#   constructor: (timeCount, persons) ->
#     @modus = 'name'
#     @personId = null
#     @personName = null
#     @personFirstname = null

#     $table = $('<table></table>').appendTo('#input-box');
#     $name = $('<input type="text" style="font-size:0.9em;width:100px;"/>');
#     $firstname = $('<input type="text" style="font-size:0.9em;width:100px;"/>');

#     $headRow = $('<tr><th>Name</th><th>Vorname</th><th>Team</th></tr>').appendTo($table);
#     $inputRow = $('<tr></tr>').append($('<td></td>').append($name)).append($('<td></td>').append($firstname)).appendTo($table);

#     teamHandler = new TeamInput(this);
#     $inputRow.append($('<td></td>').append(teamHandler.$select).append(teamHandler.$wait));

#     times = [];
#     i, l;

#     for (i = 0; i < timeCount; i++) {
#         times.push(new TimeInput(this));

#         $headRow.append('<th>Lauf ' + (i + 1) + '</th>');
#         $inputRow.append($('<td></td>').append(times[i].$input).append(times[i].$wait));
#     }

#     $name.focus();

#     oldName = $name.val();
#     oldFirstname = $firstname.val();
#     selectionLineHandler = new SelectionLineHandler($table, persons);

#     $name.add($firstname).keyup(function(e) {
#         var person;

#         if ([9,13,38,40].indexOf(e.keyCode) >= 0) {
#             switch (e.keyCode) {
#                 case 38:
#                     selectionLineHandler.up();
#                     break;

#                 case 40:
#                     selectionLineHandler.down();
#                     break;

#                 case 13:
#                     person = selectionLineHandler.getPerson();
#                     selectionLineHandler.remove();

#                     if (person !== false) {
#                         $name.val(person.name);
#                         $firstname.val(person.firstname);
#                         personId = person.id;
#                     }
#                     personName = $name.val();
#                     personFirstname = $firstname.val();

#                     $name.attr('disabled', true);
#                     $firstname.attr('disabled', true);

#                     teamHandler.toEdit(personId);

#                     break;

#                 case 9:
#                     if ($name[0] == document.activeElement) {
#                         $firstname.focus();
#                     } else if ($firstname[0] == document.activeElement) {
#                         $name.focus();
#                     }
#             }
#             return false;
#         } else {
#             if (oldName != $name.val() || oldFirstname != $firstname.val()) {

#                 oldName = $name.val();
#                 oldFirstname = $firstname.val();

#                 selectionLineHandler.search(oldName, oldFirstname);
#             }
#         };
#     });


#     $('body').keydown(function(e) {
#         if (e.keyCode == 9) {
#             return false;
#         }
#     });


#     this.nextTime = function() {
#         var i, l;

#         for (i = 0, l = times.length; i < l; i++) {
#             if (!times[i].isReady()) {
#                 times[i].toEdit();
#                 return;
#             }
#         }

#         this.ready();
#     };

#     this.ready = function() {
#         var i, l;
#         var content = $('#result-textarea').val();

#         var cols = [];
#         cols.push(personName);
#         cols.push(personFirstname);
#         cols.push(teamHandler.getTeam());


#         for (i = 0, l = times.length; i < l; i++) {
#             cols.push(times[i].getTime());
#         }


#         for (i = 0, l = cols.length; i < l; i++) {
#             cols[i] = '"' + cols[i].replace(/"/, '\\"') + '"';
#         }

#         $('#result-textarea').val($('#result-textarea').val() + "\n" + cols.join("  "));


#         $table.remove();

#         new NewRow(timeCount, persons);
#     };

# };





# });



# var TeamInput = function(rowHandler) {
#     var self = this;

#     this.$wait = $('<span style="font-size:0.7em;font-style:italic;"">Warte auf ENTER</span>');
#     this.$select = $('<select></select>').hide();

#     this.toEdit = function(personId) {
#         this.$wait.text('Warten...');


#         checkLogin(function() {
#             wPost('get-teams', {person_id: personId}, function( data ) {
#                 var i, l,
#                     teams = data.teams;
#                 var teamOption = true;

#                 teams.splice(0,0, {value: 'NULL', display: ''});

#                 for (i = 0, l = teams.length; i < l; i++) {
#                     if (teams[i].inteam) {
#                         if (teamOption) {
#                             self.$select.append('<option selected="selected" style="font-weight:bold;" value="' + teams[i].value +'">' + teams[i].display +'</option>');
#                             teamOption = false;
#                         } else {
#                             self.$select.append('<option style="font-weight:bold;" value="' + teams[i].value +'">' + teams[i].display +'</option>');
#                         }
#                     } else {
#                         self.$select.append('<option value="' + teams[i].value +'">' + teams[i].display +'</option>');
#                     }
#                 }

#                 self.$wait.hide();
#                 self.$select.show().focus();

#                 self.$select.keyup(function(e) {
#                     if (e.keyCode == 13) {
#                         self.next();
#                     }
#                     return false;
#                 });
#             });
#         });
#     };

#     this.next = function() {
#         this.$select.attr('disabled', true);
#         rowHandler.nextTime();
#     };


#     this.getTeam = function() {
#         return this.$select.val();
#     };
# };

# var TimeInput = function(rowHandler) {
#     var ready = false;
#     var self = this;

#     this.$wait = $('<span style="font-size:0.7em;font-style:italic;"">Warte auf ENTER</span>');
#     this.$input = $('<input type="text" style="font-size:0.9em;width:100px;"/>').hide();

#     this.isReady = function() {
#         return ready;
#     };

#     this.toEdit = function() {
#         this.$wait.hide();
#         this.$input.show();

#         this.$input.keyup(function(e) {
#             if (e.keyCode == 13 && self.isReady()) {
#                 self.$input.attr('disabled', true);

#                 rowHandler.nextTime();
#                 return false;
#             }

#             var time = $.trim(self.$input.val());
#             time = time.replace(/,,/, ',');
#             time = time.toUpperCase();
#             if (time.match(/^[0-9]{2}$/)) {
#                 time = time + ',';
#             }

#             if (time != self.$input.val()) {
#                 self.$input.val(time);
#             }

#             if (time.match(/^[0-9]{2,3},[0-9]{2}$/) || time.match(/^d|n$/i)) {
#                 ready = true;
#                 self.$input.css('background-color', '#90EE90');
#             } else {
#                 ready = false;
#                 self.$input.css('background-color', '');
#             }

#         });

#         this.$input.focus();
#     };

#     this.getTime = function() {
#         return this.$input.val();
#     };
# };

# var SelectionLineHandler = function($table, personsList) {
#     var oldName = '';
#     var oldFirstname = '';

#     var selected = -1;

#     var persons = [];
#     var i, l;
#     for (i = 0, l = personsList.length; i < l; i++) {
#         persons.push({});
#         persons[i].name = personsList[i].name;
#         persons[i].firstname = personsList[i].firstname;
#         persons[i].id = personsList[i].id;
#         persons[i].tName = function(name) {
#             return (this.name.indexOf(name) != -1) || (this.name.toLowerCase().indexOf(name) != -1);
#         };
#         persons[i].tFirstname = function(name) {
#             return (this.firstname.indexOf(name) != -1) || (this.firstname.toLowerCase().indexOf(name) != -1);
#         };
#     }


#     var lines = [];

#     this.up = function() {
#         if (selected <= -1) return;

#         selected--;
#         this.setHighlightRow();
#     };

#     this.down = function() {
#         if (selected >= lines.length) return;

#         selected++;
#         this.setHighlightRow();
#     };


#     this.setHighlightRow = function() {
#         var i, l;

#         for (i = 0, l = lines.length; i < l; i++) {
#             if (i == selected) {
#                 lines[i].$row.css('background-color', '#90EE90');
#             } else {
#                 lines[i].$row.css('background-color', '');
#             }
#         }
#     };

#     this.remove = function() {
#         var i, l;

#         for (i = 0, l = lines.length; i < l; i++) {
#             lines[i].$row.remove();
#         }
#         lines = [];
#     };

#     this.getPerson = function() {
#         if (selected == -1) return false;
#         return lines[selected].person;
#     };

#     this.search = function (name, firstname) {
#         var i, l;
#         var line;
#         var toDelete = [];

#         name = $.trim(name);
#         firstname = $.trim(firstname);

#         selected = -1;


#         if (name == '' && firstname == '') {
#             this.remove();
#             oldName = name;
#             oldFirstname = firstname;

#             return;
#         }

#         if ((oldName != '' || oldFirstname != '') &&
#                 name.indexOf(oldName) != -1 && firstname.indexOf(oldFirstname) != -1) {

#             // search in lines
#             for (i = lines.length - 1; i >= 0; i--) {
#                 if (!lines[i].person.tName(name) || !lines[i].person.tFirstname(firstname)) {
#                     lines[i].$row.remove();
#                     lines.splice(i, 1);
#                 }
#             }

#             this.setHighlightRow();

#         } else {

#             // search in all persons

#             // first delete old lines
#             this.remove();

#             // add all match persons
#             for (i = 0, l = persons.length; i < l; i++) {
#                 if (persons[i].tName(name) && persons[i].tFirstname(firstname)) {
#                     line = {
#                         $row: $('<tr><td>' + persons[i].name + '</td><td>' + persons[i].firstname + '</td></tr>'),
#                         person: persons[i]
#                     };
#                     $table.append(line.$row);
#                     lines.push(line);
#                 }
#             }
#         }

#         oldName = name;
#         oldFirstname = firstname;
#     };
# };


