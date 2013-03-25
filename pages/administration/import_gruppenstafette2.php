<?php

if (isset($_POST['step'])) {

  if ($_POST['step'] == 'save') {
    $persons = array();

    for ($i=0; $i < count($_POST['teams']); $i++) {
      $persons[$i] = array(
        'team' => $_POST['teams'][$i],
        'number' => strval(intval($_POST['numbers'][$i]) -1),
        'time0' => $_POST['times0'][$i],
        'time1' => $_POST['times1'][$i],
        'time2' => $_POST['times2'][$i],
        'id' => null
      );
    }


    foreach ($persons as $person) {




        if ($person['team'] == -1) {
            $person['team'] = NULL;
            print_r($person);
            continue;
        }

        for($i = 0; $i < 3; $i++) {
            if ($person['time'.$i] == 'NULL') {
                $person['time'.$i] = NULL;
            } elseif ($person['time'.$i] == '-1') {
                continue;
            }
              // insert score
              $db->insertRow('scores_gruppenstafette', array(
                'competition_id' => $_POST['competition'],
                'time' => $person['time'.$i],
                'team_id' => $person['team'],
                'team_number' => $person['number'],
              ));
          }

    }

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


    echo '<ul class="disc" id="add-team"></ul>';

    echo '<table id="scores" class="table"><tr><th>Team</th><th>Zeit</th><th>Zeit2</th><th>Zeit3</th><th>Num</th><th>Old</th><th>Eingabe</th></tr>';
    $scores = explode("\n", $_POST['scores']);


    $seperator = "\t";

    if ($_POST['seperator'] == 'tab') {
        $seperator = "\t";
    } elseif ($_POST['seperator'] == 'space') {
        $seperator = " ";
    } else {
        $seperator = ",";
    }

    $ths = explode(",", $_POST['spalten']);

    foreach($scores as $score) {
        $correct = true;
        $score = trim($score);

        $cols = str_getcsv($score, $seperator);

        $times = array();
        $time = '0';
        $team = '';
        $team_id = '-1';
        $oldteam = '';

        if (count($cols) < count($ths)) {
            $name = '';
            $firstname = '';
            $time = '0';
            $team = '';
            $number = '1';
            $oldteam = '';
            $correct = false;
        } else {
            $number = '1';

            for ($i = 0; $i < count($ths); $i++) {
                $cols[$i] = trim($cols[$i]);
                switch ($ths[$i]) {
                    case 'time':
                    case 'time2':
                    case 'time3':
                        $time = $cols[$i];

                        if ($time == 'N') {
                            $time = -1;
                        } else {
                            if (!preg_match('|^[\d,:;.]+$|', trim($time))) {
                                $correct = false;
                            }

                            if (preg_match('|^(\d+):(\d{2})[:,](\d{2})$|', trim($time), $arr)) {
                                $time = (intval($arr[1])*60+intval($arr[2])).':'.$arr[3];
                            }

                            if (strpos($time, ',') !== false || strpos($time, '.') !== false) {
                                $time = str_replace(',','.',$time);
                                $time = str_replace(':','',$time);
                                $time = str_replace(';','',$time);
                                $time = floatval($time) *100;
                            } elseif (strpos($time, ';') !== false || strpos($time, ':')) {
                                $time = str_replace(';','.',$time);
                                $time = str_replace(':','.',$time);
                                $time = floatval($time) *100;
                            } elseif (is_numeric($time)) {
                                $time = intval($time);

                                if ($time > 98) $correct = false;
                                else $time *= 100;
                            }


                            if (is_numeric($time) && $time < 1200) {
                                $correct = true;
                                $time = 'NULL';
                            }

                            if (is_numeric($time) && $time > 99800) {
                                $correct = true;
                                $time = 'NULL';
                            }

                            if ($time == 'D' || $time == 'd') {
                                $correct = true;
                                $time = 'NULL';
                            }
                        }

                        $times[] = $time;
                        $time = '0';


                        break;

                    case 'team':
                        $team = trim($cols[$i]);
                        $oldteam = $team;


                        if (preg_match('/ 1$/', $team) || preg_match('/ I$/', $team)) {
                            $number = 1;
                        } elseif (preg_match('/ 2$/', $team) || preg_match('/ II$/', $team)) {
                            $number = 2;
                        } elseif (preg_match('/ 3$/', $team) || preg_match('/ III$/', $team)) {
                            $number = 3;
                        }


                        if (is_numeric($team)) {
                            $team_db = $db->getFirstRow("
                                SELECT *
                                FROM `teams`
                                WHERE `id` = '".$db->escape($team)."'
                                LIMIT 1;");
                        } else {
                            $team = preg_replace('/^FF /', '', $team);
                            $team = preg_replace('/^Team /', '', $team);
                            $team = preg_replace('/ 1$/', '', $team);
                            $team = preg_replace('/ 2$/', '', $team);
                            $team = preg_replace('/ 3$/', '', $team);
                            $team = preg_replace('/ I$/', '', $team);
                            $team = preg_replace('/ II$/', '', $team);

                            $team_db = $db->getFirstRow("
                                SELECT *
                                FROM `teams`
                                WHERE `name` LIKE '".$db->escape($team)."'
                                OR `short` LIKE '".$db->escape($team)."'
                                OR CONCAT(`name`,' I') LIKE '".$db->escape($team)."'
                                OR CONCAT(`name`,' II') LIKE '".$db->escape($team)."'
                                OR CONCAT(`name`,' III') LIKE '".$db->escape($team)."'
                                OR CONCAT(`name`,' 1') LIKE '".$db->escape($team)."'
                                OR CONCAT(`name`,' 2') LIKE '".$db->escape($team)."'
                                OR REPLACE(`name`,'FF ','') LIKE '".$db->escape($team)."'
                                OR REPLACE(`name`,'TEAM ','') LIKE '".$db->escape($team)."'
                                OR CONCAT(REPLACE(`name`,'TEAM ',''),' I') LIKE '".$db->escape($team)."'
                                OR CONCAT(REPLACE(`name`,'TEAM ',''),' II') LIKE '".$db->escape($team)."'
                                LIMIT 1;");
                        }

                        if ($team_db) {
                            $team_id = $team_db['id'];
                            $team = $team_db['short'];
                        } else {

                            switch ($team) {

                                case 'Team Meckl.-Vorp. I':
                                case 'Team Meckl.-Vorp. II':
                                case 'Mecklenburg-Vorpommern':
                                case 'Mecklenburg-Vorpommern I':
                                case 'Mecklenburg-Vorpommern II':
                                case 'Meckl.-Vorp.':
                                case 'Mecklenb.-Vorp.':
                                case 'Team-MV':
                                case 'Meckl.-Vorpomm.':
                                case 'MV':
                                case 'MV 1':
                                case 'MV 2':
                                case 'Meckl.-Vorpommern':
                                case 'Team Meckl.-Vorp.':
                                    $team = 'Team MV';
                                    $team_id = 2;
                                break;


                                case 'Halle':
                                case 'Feuerwehr Halle':
                                    $team = 'Halle';
                                    $team_id = 8;
                                break;


                                case 'Nord-West-Sachsen':
                                    $team = 'NW-Sachsen';
                                    $team_id = 14;
                                break;


                                case 'Gamstedt-Stelzendorf':
                                case 'Gamstädt-Stelzendorf':
                                    $team = 'Gamstädt/Stelzendorf';
                                    $team_id = 39;
                                break;

                                case 'TSV Zeulenroda':
                                    $team = 'Zeulenroda';
                                    $team_id = 15;
                                break;

                                case 'Team Märkisch-Oderland 1':
                                case 'Märkisch-Oderl.':
                                case 'Märk.Oderland':
                                case 'Team Märkisch-Oderland':
                                case 'Märk.-Oderland':
                                case 'Märk.-Oderl.':
                                    $team = 'MOL';
                                    $team_id = 10;
                                break;

                                case 'Sachsen-Anhalt':
                                case 'Anhalt 1':
                                case 'Anhalt 2':
                                case 'Anhalt':
                                    $team = 'SA';
                                    $team_id = 20;
                                break;

                                case 'Burkersdorf':
                                    $team = 'Burkersdorf';
                                    $team_id = 19;
                                break;

                                case 'Südthüringen-Auswahl':
                                case 'Südthüringen-':
                                case 'Süd-Thüringen-Auswah':
                                    $team = 'Südthüringen';
                                    $team_id = 37;
                                break;

                                case 'Halle-Thalheim':
                                    $team = 'Halle-Thalheim';
                                    $team_id = 38;
                                break;

                                case 'Gamstädt':
                                    $team = 'Gamstädt';
                                    $team_id = 16;
                                break;

                                case 'Leipzig':
                                    $team = 'Leipzig';
                                    $team_id = 25;
                                break;

                                case 'Muldenthal':
                                    $team = 'Muldental';
                                    $team_id = 12;
                                break;

                                case 'Team Nudersdorf':
                                    $team = 'Nudersdorf';
                                    $team_id = 30;
                                break;

                                case 'Taura':
                                case 'Taura 1':
                                case 'Taura 2':
                                    $team = 'Taura';
                                    $team_id = 13;
                                break;

                                case 'Team - Lausitz [E]':
                                case 'Team Lausitz [E]':
                                case 'Lausitz':
                                case 'Lausitz 1':
                                case 'Lausitz 2':
                                    $team = 'Lausitz';
                                    $team_id = 3;
                                break;

                                case 'Thüringenauswahl':
                                case 'Thüringen Auswahl 1':
                                case 'Thüringen Auswahl':
                                case 'Thüringen-':
                                case 'Thüringen Auswahl 2':
                                    $team = 'Thüringen';
                                    $team_id = 11;
                                break;


                                default:
                                    $correct = false;
                                    $team = '';
                                    $team_id = '-1';
                                break;
                            }
                        }
                        break;
                }
            }
        }

        echo '<tr class="';

        if ($correct) echo 'correct';
        else echo 'notcorrect';

        echo '">';

        echo '<td class="team"';
        if ($correct && $team_id < 0) echo ' style="background-color:#B1FFB1"';
        echo ' data-id="'.$team_id.'">'.$team.'</td>';

        for($i = 0; $i < 3; $i++) {
            if (isset($times[$i])) echo '<td class="time'.$i.'">'.$times[$i].'</td>';
            else echo '<td class="time'.$i.'">-1</td>';
        }
        echo '<td style="font-size:0.8em" class="number">'.$number.'</td>';

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
      teams = [];

    $('tr.correct').each(function(i, elem) {
      times0.push($(this).find('.time0').text());
      times1.push($(this).find('.time1').text());
      times2.push($(this).find('.time2').text());
      numbers.push($(this).find('.number').text());
      teams.push($(this).find('.team').data('id'));
    });

    $.post('?page=administration&admin=import_gruppenstafette2',{
      'step': 'save',
      'competition': '<?php echo $_POST['competition']; ?>',
      'times0[]': times0,
      'times1[]': times1,
      'times2[]': times2,
      'teams[]': teams,
      'numbers[]': numbers
    }, function(data) {
        if (data.indexOf('SUCCESS ---- SUCCESS') > 0) {
            window.location = '?page=administration&admin=import_gruppenstafette2'
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
  ORDER BY `c`.`date` DESC;
");


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
    <h3>Zeiten</h3>
    <textarea name="scores" style="width:1000px"></textarea>
    <input type="text" name="spalten" value="team,time" style="width:500px"/><br/>
    <input type="radio" value="tab" name="seperator" id="seperator-tab"/><label for="seperator-tab">tab</label><br/>
    <input type="radio" value="space" name="seperator" id="seperator-space"/><label for="seperator-space">space</label><br/>
    <input type="radio" value="comma" name="seperator" id="seperator-comma"/><label for="seperator-comma">comma</label>
    <h4>Erklärung</h4>
    <ul class="disc">
        <li>col</li>
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
