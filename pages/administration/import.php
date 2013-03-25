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
        ));
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

    echo '<h3>Disziplin: '.$_POST['discipline'].'</h3>';
    echo '<h3>Geschlecht: '.$_POST['sex'].'</h3>';

    echo '<table id="scores" class="table"><tr><th>Vorname</th><th>Nachname</th><th>Zeit</th><th>Eingabe</th></tr>';
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
                        if (!preg_match('|^[\d,:;.]+$|', trim($cols[$i]))) {
                            $correct = false;
                        }
                        $time = $cols[$i];

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

                        if (is_numeric($time) && $time > 9980) {
                            $correct = true;
                            $time = 'NULL';
                        }

                        if ($time == 'D' || $time == 'd') {
                            $correct = true;
                            $time = 'NULL';
                        }

                        $times[] = $time;
                        $time = '0';

                        break;

                    case 'team':
                        $team = trim($cols[$i]);
                        $oldteam = $team;

                        if (is_numeric($team)) {
                            $team_db = $db->getFirstRow("
                                SELECT *
                                FROM `teams`
                                WHERE `id` = '".$db->escape($team)."'
                                LIMIT 1;");
                        } else {

                            if (preg_match('/ 1$/', $team) || preg_match('/ I$/', $team)) {
                                $number = 1;
                            } elseif (preg_match('/ 2$/', $team) || preg_match('/ II$/', $team)) {
                                $number = 2;
                            } elseif (preg_match('/ 3$/', $team) || preg_match('/ III$/', $team)) {
                                $number = 3;
                            }

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

        // search person
        $result_search = $db->getFirstRow("
            SELECT *
            FROM `persons`
            WHERE `name` = '".$db->escape($name)."'
            AND `firstname` = '".$db->escape($firstname)."'
            AND `sex` = '".$db->escape($_POST['sex'])."'");


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
        echo '<td style="font-size:0.8em">'.$oldteam.'</td>';

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
