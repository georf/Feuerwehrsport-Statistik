<?php

if (isset($_POST['step'])) {

  if ($_POST['step'] == 'test') {
      print_r($_POST);
    $rows = array();

    for ($i=0; $i < count($_POST['team']); $i++) {
       if (!Check::isIn($_POST['team'][$i], 'teams')) continue;
       if (!Check::isIn($_POST['competition'], 'competitions')) continue;
       if (!in_array($_POST['sex'], array('male','female'))) continue;

       for($z = 0; $z < 5; $z++) {
           if (isset($_POST['time'.$z][$i])) {

               if ($_POST['time'.$z][$i] == 'D') $time = NULL;
               else {
                    $time = intval($_POST['time'.$z][$i]);
                    if ($time < 2000) continue;
                }


                echo '<br>INSERT<br/>';
                $insert = array(
                'team_id' => $_POST['team'][$i],
                'team_number' => $_POST['team_number'][$i],
                'time' => $time,
                'competition_id' => $_POST['competition'],
                'sex' => $_POST['sex']
                );

                print_r($insert);
              echo $db->insertRow('scores_la', $insert);
            }
        }

    }


    echo 'SUCCESS ---- SUCCESS';



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

$teams = $db->getRows("
    SELECT *
    FROM `teams`
    ORDER BY `name`
");

?>


<div class="container">
  <form method="post" action="">
    <input type="hidden" name="step" value="test"/>
    <h3>Wettkampf</h3>
    <h3>Zeiten</h3>
    <table>
        <?php
        for($i = 0; $i < 20; $i++) {
            ?>
        <tr><td>
        <select name="team[]">
            <?php
            foreach ($teams as $team) {
                echo '<option value="'.$team['id'].'">'.$team['name'].'</option>';
            }
            ?>
        </select>
        <select name="team_number[]">
            <option value="0" selected="selected">1</option>
            <option value="1">2</option>
            <option value="2">3</option>
        </select>
        <input name="time1[]">
        <input name="time2[]">
        </td></tr>
    <?php } ?>
    </table>
    <select name="competition">
        <?php
          foreach ($competitions as $competition) {
            echo '<option value="'.$competition['id'].'">',$competition['date'],' - ',$competition['event'],' - ',$competition['place'],'</option>';
          }
        ?>
    </select>
    <select name="sex">
		<option></option>
		<option>male</option>
		<option>female</option>
    </select>
  <button type="submit">Testen</button>
  </form>

</div>

<?php
}
