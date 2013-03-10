
<?php
if (isset($_POST['step']) && $_POST['step'] == 'save') {
    $lines = array();

    $scores = explode("\n", $_POST['csv-data']);
    $seperator = "\t";

    foreach($scores as $score) {
        $correct = true;
        $score = trim($score);

        $cols = str_getcsv($score, $seperator);

        if (count($cols) >= 4) {


            if ($cols[3] == 'NULL' || $cols[3] == 'D' || $cols[3] == 'N') {
                $cols[3] = NULL;
            }


            $db->insertRow('scores_stafette', array(
                'team_id' => $cols[0],
                'team_number' => intval($cols[2])-1,
                'run' => $cols[1],
                'sex' => $_POST['sex'],
                'time' => $cols[3],
                'competition_id' => $_POST['competition'],
                'id' => null
              ));
        }
    }

    print_r($lines);

    echo 'SUCCESS ---- SUCCESS';



}
?>
<div id="form-box">

    <div class="four columns">
        <h3>Geschlecht</h3>
        <input type="radio" name="sex" value="male" id="sex"/><label for="sex">Männlich</label><br/>
        <input type="radio" name="sex" value="female" id="sex2"/><label for="sex2">Weiblich</label>
    </div>

    <div class="ten columns" id="button-box" style="margin:40px;">
        <button>Zeiten eintragen</button>
    </div>
</div>

<div id="input-box" class="row" style="min-height:500px">
</div>

<div id="result-box">
    <form method="post" action="">
    <textarea id="result-textarea" style="width:80%;" name="csv-data"></textarea>
<?php


$competitions = $db->getRows("
  SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`
  FROM `competitions` `c`
  INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
  INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
  ORDER BY `c`.`date` DESC;

");
?>
    <select name="competition">
        <?php
          foreach ($competitions as $competition) {
            echo '<option value="'.$competition['id'].'">',$competition['date'],' - ',$competition['event'],' - ',$competition['place'],'</option>';
          }
        ?>
    </select>
    <input type="hidden" name="sex" id="hidden-sex"/>
    <button type="submit" name="step" value="save">Speichern</button>
    </form>
</div>


<script type="text/javascript">
$(function() {
    var sex = null;
    var count = 1;

    var checkForm = function() {
        var correct = true;

        sex = $("input[name='sex']:checked").val();
        if (sex == 'male') {
        } else if (sex == 'female') {
        } else {
            correct = false;
        }

        if (correct) {
            $('#button-box').show();
        }

    };

    $('#button-box').hide().click(function() {
        $('#form-box').hide();

        checkLogin(function() {
            wPost('get-teams', {} , function( data ) {

                $('#hidden-sex').val(sex);

                var teams = data.teams;
                new NewRow(teams);
            });
        });
    });



var TeamInput = function(rowHandler) {
    var self = this;

    this.$wait = $('<span style="font-size:0.7em;font-style:italic;"">Warte auf ENTER</span>');
    this.$select = $('<select></select>').hide();

    this.toEdit = function(personId) {
        this.$wait.text('Warten...');


        checkLogin(function() {
            wPost('get-teams', {personId: personId}, function( data ) {
                var i, l,
                    teams = data.teams;
                var teamOption = true;

                teams.splice(0,0, {value: 'NULL', display: ''});

                for (i = 0, l = teams.length; i < l; i++) {
                    if (teams[i].inteam) {
                        if (teamOption) {
                            self.$select.append('<option selected="selected" style="font-weight:bold;" value="' + teams[i].value +'">' + teams[i].display +'</option>');
                            teamOption = false;
                        } else {
                            self.$select.append('<option style="font-weight:bold;" value="' + teams[i].value +'">' + teams[i].display +'</option>');
                        }
                    } else {
                        self.$select.append('<option value="' + teams[i].value +'">' + teams[i].display +'</option>');
                    }
                }

                self.$wait.hide();
                self.$select.show().focus();

                self.$select.keyup(function(e) {
                    if (e.keyCode == 13) {
                        self.next();
                    }
                    return false;
                });
            });
        });
    };

    this.next = function() {
        this.$select.attr('disabled', true);
        rowHandler.nextTime();
    };


    this.getTeam = function() {
        return this.$select.val();
    };
};

var TimeInput = function(rowHandler, $time) {
    var ready = false;
    var self = this;

    $time.keyup(function(e) {
        if (e.keyCode == 13 && ready) {
            rowHandler.ready($time.val());
            return false;
        }

        var time = $.trim($time.val());
        time = time.replace(/,/, '');
        time = time.toUpperCase();

        if (time != $time.val()) {
            $time.val(time);
        }

        if (time.match(/^[0-9]{4,5}$/) || time.match(/^d|n$/i)) {
            ready = true;
            $time.css('background-color', '#90EE90');
        } else {
            ready = false;
            $time.css('background-color', '');
        }

    });

    $time.focus();
};


var NewRow = function(teams) {

    var modus = 'name';

    var teamId = null;

    // generate html
    var $table = $('<table></table>').appendTo('#input-box');
    var $select = $('<select></select>');
    var $staffel = $('<select></select>');
    var $time = $('<input type="text"/>');

    var $headRow = $('<tr><th>Team</th><th>Staffel</th><th>Zeit</th></tr>').appendTo($table);
    var $inputRow = $('<tr></tr>').append($('<td></td>').append($select)).append($('<td></td>').append($staffel)).appendTo($table);
    $inputRow.append($('<td></td>').append($time));

    var self = this;
    var i;

    // add teams
    for (i = 0; i < teams.length; i++) {
        $select.append('<option value="' + teams[i].value + '">' + teams[i].display +'</option>');
    }

    $select.focus().keyup(function(e) {
        var person;

        if (e.keyCode == 9) {

            for (i = 0; i < teams.length; i++) {
                if (teams[i].value == $select.val()) {
                    if (!teams[i].A1)
                        $staffel.append('<option value="A1">A1</option>');

                    if (!teams[i].B1)
                        $staffel.append('<option value="B1">B1</option>');

                    if (!teams[i].A2)
                        $staffel.append('<option value="A2">A2</option>');

                    if (!teams[i].B2)
                        $staffel.append('<option value="B2">B2</option>');

                    break;
                }
            }

            $staffel.focus();
            $select.attr('disabled',true);

            $staffel.keyup(function(e) {
                if (e.keyCode == 9) {
                    new TimeInput(self, $time);
                }
            });

            return false;
        }
    });


    $('body').keydown(function(e) {
        if (e.keyCode == 9) {
            return false;
        }
    });

    this.ready = function(time) {
        var i, l;
        var staffel = $staffel.val();
        var content = $('#result-textarea').val();

        var cols = [];




        for (i = 0; i < teams.length; i++) {
            if (teams[i].value == $select.val()) {
                cols.push(teams[i].value);
                teams[i][staffel] = true;
                break;

            }
        }


        cols.push(staffel.replace(/[0-9]/,''));
        cols.push(staffel.replace(/[a-zA-Z]/,''));
        cols.push(time);

        for (i = 0, l = cols.length; i < l; i++) {
            cols[i] = '"' + cols[i].replace(/"/, '\\"') + '"';
        }

        $('#result-textarea').val($('#result-textarea').val() + "\n" + cols.join("	"));


        $table.remove();

        new NewRow(teams);
    };

};





    $('#sex, #sex2').change(function() {
        checkForm();
    });
});
</script>
