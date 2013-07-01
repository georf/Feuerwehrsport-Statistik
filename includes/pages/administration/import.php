<?php

if (isset($_POST['step'])) {

    if ($_POST['step'] == 'save') {
        $persons = array();

        for ($i=0; $i < count($_POST['names']); $i++) {
            $persons[$i] = array(
                'name' => $_POST['names'][$i],
                'firstname' => $_POST['firstnames'][$i],
                'team' => $_POST['teams'][$i],
                'time0' => $_POST['times0'][$i],
                'time1' => $_POST['times1'][$i],
                'time2' => $_POST['times2'][$i],
                'number' => strval(intval($_POST['numbers'][$i]) -1),
                'id' => null
            );
        }

        foreach ($persons as $person) {
            // search person
            $result = $db->getFirstRow("
                SELECT *
                FROM `persons`
                WHERE `name` = '".$db->escape($person['name'])."'
                AND `firstname` = '".$db->escape($person['firstname'])."'
                AND `sex` = '".$db->escape($_POST['sex'])."'");

            if ($result) {
                $person['id'] = $result['id'];
            } else {
                // insert
            $result = $db->insertRow('persons', array(
                'name' => $person['name'],
                'firstname' => $person['firstname'],
                'sex' => $_POST['sex']
            ), false);
            $person['id'] = $result;
        }


        if ($person['team'] == -1) {
            $person['team'] = NULL;
        }


        for($i = 0; $i < 3; $i++) {
            if ($person['time'.$i] == 'NULL') {
                $person['time'.$i] = NULL;
            } elseif ($person['time'.$i] == '-1') {
                continue;
            }

            // insert score
            $db->insertRow('scores', array(
                'person_id' => $person['id'],
                'competition_id' => $_POST['competition'],
                'discipline' => $_POST['discipline'],
                'time' => $person['time'.$i],
                'team_number' => $person['number'],
                'team_id' => $person['team']
            ), false);
        }
    }

    Cache::clean();

    echo 'SUCCESS ---- SUCCESS';



  } elseif ($_POST['step'] == 'test') {

    $competition = $db->getFirstRow("
      SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`
      FROM `competitions` `c`
      INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
      INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
      WHERE `c`.`id` = '".$_POST['competition']."'
    ");

    echo '<h3>Wettkampf: ',$competition['date'],' - ',$competition['event'],' - ',$competition['place'],'</h3>';

    echo '<h3>Disziplin: '.$_POST['discipline'].'</h3>';
    echo '<h3>Geschlecht: '.$_POST['sex'].'</h3>';
    echo '<ul class="disc" id="add-team"></ul>';

    echo '<table id="scores" class="table"><tr><th>Vorname</th><th>Nachname</th><th>Zeit</th><th>Zeit 2</th><th>Team</th><th>O. Team</th></tr>';
    $scores = explode("\n", $_POST['scores']);


    $seperator = "\t";

    if ($_POST['seperator'] == 'tab') {
        $seperator = "\t";
    } else {
        $seperator = ",";
    }

    $ths = explode(",", $_POST['spalten']);

    foreach($scores as $score) {
        $correct = true;
        $score = trim($score);

        $cols = str_getcsv($score, $seperator);

        $times = array();
        $name = '';
        $firstname = '';
        $time = '0';
        $team = '';
        $team_id = '-1';
        $oldteam = '';
        $number = '1';

        if (count($cols) < count($ths)) {
            $name = '';
            $firstname = '';
            $time = '0';
            $team = '';
            $oldteam = '';
            $correct = false;
        } else {

            for ($i = 0; $i < count($ths); $i++) {
                $cols[$i] = trim($cols[$i]);
                switch (trim($ths[$i])) {
                    case 'name':
                        $name = preg_replace('|,$|','',preg_replace('|^,|','',trim($cols[$i])));
                        break;

                    case 'firstname':
                        $firstname = preg_replace('|,$|','',preg_replace('|^,|','',trim($cols[$i])));
                        break;

                    case 'time':
                    case 'time2':
                    case 'time3':
                    case 'time4':
                    case 'time5':
                        $time = Import::getTime($cols[$i]);
                        if ($time === null) {
                            $time = 'NULL';
                        } elseif ($time === false) {
                            $time = -1;
                        }
                        $times[] = $time;
                        break;

                    case 'team':
                        $team = trim($cols[$i]);
                        $oldteam = $team;


                        $number = Import::getTeamNumber($team, $number);

                        if (is_numeric($team) && Check::isIn($team, 'teams')) {
                            $team_id = $team;
                            break;
                        }

                        $test_id = Import::getTeamId($team);
                        if ($test_id !== false) {
                            $team_id = $test_id;
                            $test_id = FSS::tableRow('teams', $test_id);
                            $team = $test_id['short'];
                            break;
                        }

                        $correct = false;
                        break;
                }
            }
        }

        echo '<tr class="';

        if ($correct) echo 'correct';
        else echo 'notcorrect';

        // search person
        $result_search = $db->getFirstRow("
            SELECT *
            FROM `persons`
            WHERE `name` = '".$db->escape($name)."'
            AND `firstname` = '".$db->escape($firstname)."'
            AND `sex` = '".$db->escape($_POST['sex'])."'");

        if (!$result_search) {
            $result_search = $db->getFirstRow("
                SELECT `p`.`name`,`p`.`firstname`
                FROM `persons_spelling` `s`
                INNER JOIN `persons` `p` ON `p`.`id` = `s`.`person_id`
                WHERE `s`.`name` = '".$db->escape($name)."'
                AND `s`.`firstname` = '".$db->escape($firstname)."'
                AND `s`.`sex` = '".$db->escape($_POST['sex'])."'");
            if ($result_search) {
                $name = $result_search['name'];
                $firstname = $result_search['firstname'];
            }
        }



        echo '">';
        echo '<td class="firstname" ';

        if (!$result_search) echo ' style="color:blue;"';

        echo '>'.$firstname.'</td>';
        echo '<td class="name" ';

        if (!$result_search) echo ' style="color:blue;"';

        echo '>'.$name.'</td>';


        for($i = 0; $i < 3; $i++) {
            if (isset($times[$i])) echo '<td class="time'.$i.'">'.$times[$i].'</td>';
            else echo '<td class="time'.$i.'">-1</td>';
        }
        echo '<td style="font-size:0.8em" class="number">'.$number.'</td>';

        echo '<td class="team"';
        if ($correct && $team_id < 0) echo ' style="background-color:#B1FFB1"';
        echo ' data-id="'.$team_id.'">'.$team.'</td>';
        echo '<td style="font-size:0.8em" class="oldteam">'.$oldteam.'</td>';

        echo '<td style="font-size:0.8em">'.$score.'</td>';
        echo '</tr>';
    }

    echo '</table>';
    ?>
    <button id="send">Speichern</button>
<script type="text/javascript">
$(function(){
    $('#scores td').each(function() {
        if ($(this).text() == 'NULL') {
            $(this).css('background', '#FFFF00');
        }
    });

  $('#scores tr').click(function() {
    $(this).toggleClass('notcorrect').toggleClass('correct');
  });

    $('.team').each(function() {
      var id = $(this).data('id');
      var name = $(this).closest('tr').find('.oldteam').text();
      if (id == '-1' && name != '') {
          var li = $('<li>' + name + '</li>');
          li.click(function() {
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
                    ], 'Mannschaft anlegen', 'Falls du ein Foto oder Icon zu dieser Mannschaft zuordnen willst, kann du es per E-Mail an den Administrator senden.')
                    .open().submit(function(data) {
                        this.close();

                        wPost('add-team', data, function() {
                            location.reload();
                        });
                    });;
                });
          });
          $('#add-team').append(li);
    }
  });

  $('#send').click(function() {
    var
      times0 = [],
      times1 = [],
      times2 = [],
      numbers = [],
      names = [],
      firstnames = [],
      teams = [];

    $('tr.correct').each(function(i, elem) {
      var e = {}
      names.push($(this).find('.name').text());
      firstnames.push($(this).find('.firstname').text());
      times0.push($(this).find('.time0').text());
      times1.push($(this).find('.time1').text());
      times2.push($(this).find('.time2').text());
      numbers.push($(this).find('.number').text());
      teams.push($(this).find('.team').data('id'));
    });

    var w = new FormWindow();
    w.open();

    $.post('?page=administration&admin=import',{
      'step': 'save',
      'sex': '<?php echo $_POST['sex']; ?>',
      'competition': '<?php echo $_POST['competition']; ?>',
      'discipline': '<?php echo $_POST['discipline']; ?>',
      'names[]': names,
      'firstnames[]': firstnames,
      'times0[]': times0,
      'times1[]': times1,
      'times2[]': times2,
      'teams[]': teams,
      'numbers[]': numbers
    }, function(data) {
        w.close();
        if (data.indexOf('SUCCESS ---- SUCCESS') > 0) {
            window.location = '?page=administration&admin=import'
        } else {
            alert(data);
        }
    });

  });
});
</script>
    <?php

  } else {
    unset($_POST);
  }

}


if (!isset($_POST['step'])) {



$competitions = $db->getRows("
  SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`
  FROM `competitions` `c`
  INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
  INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
  ORDER BY `c`.`id` DESC;
");

$disciplines = array(
    array('id' => 'HB', 'name' => 'Hindernisbahn'),
    array('id' => 'HL', 'name' => 'Hakenleiter'),
);

?>


<div class="container">
  <form method="post" action="">
    <input type="hidden" name="step" value="test"/>
    <h3>Wettkampf</h3>
    <select name="competition">
        <?php
          foreach ($competitions as $competition) {
            echo '<option value="'.$competition['id'].'">',$competition['date'],' - ',$competition['event'],' - ',$competition['place'],'</option>';
          }
        ?>
    </select>
    <h3>Disziplin</h3>
    <select name="discipline">
        <?php
          foreach ($disciplines as $discipline) {
            echo '<option value="'.$discipline['id'].'">',$discipline['name'],'</option>';
          }
        ?>
    </select>
    <h3>Geschlecht</h3>
    <select name="sex">
      <option value="male">Männlich</option>
      <option value="female">Weiblich</option>
    </select>
    <h3>Zeiten</h3>
    <textarea name="scores" style="width:1000px"></textarea>
    <input type="text" name="spalten" value="name,firstname,team,time,time2" style="width:500px"/><br/>
    <input type="radio" value="tab" name="seperator" id="seperator-tab"/><label for="seperator-tab">tab</label><br/>
    <input type="radio" value="comma" name="seperator" id="seperator-comma"/><label for="seperator-comma">comma</label>
    <h4>Erklärung</h4>
    <ul class="disc">
        <li>col</li>
        <li>firstname</li>
        <li>name</li>
        <li>time</li>
        <li>time2</li>
        <li>time3</li>
        <li>team</li>
    </ul>
  <button type="submit">Testen</button>
  </form>

</div>

<?php
}
