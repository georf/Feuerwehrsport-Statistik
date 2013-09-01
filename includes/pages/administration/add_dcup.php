<?php

if (Check::post('competition', 'team', 'number','sex')) {
    foreach (array('HL','HB', 'GS','FS','LA') as $d) {
        if (Check::post($d)) {
            $db->insertRow('scores_team_dcup', array(
                'competition_id' => $_POST['competition'],
                'team_id' => $_POST['team'],
                'team_number' => $_POST['number'],
                'sex' => $_POST['sex'],
                'points' => $_POST[$d],
                'discipline' => $d,
            ));
        }
    }
}

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
    <h3>Wettkampf</h3>
    <select name="sex">
        <option value="female">weiblich</option>
        <option value="male">männlich</option>
    </select>
    <select name="competition">
        <?php
          foreach ($competitions as $competition) {
            echo '<option value="'.$competition['id'].'"';
            if (isset($_POST['competition']) && $_POST['competition'] == $competition['id']) echo ' selected="selected"';
            echo '>',$competition['date'],' - ',$competition['event'],' - ',$competition['place'],'</option>';
          }
        ?>
    </select>
    <select name="team">
        <?php
          foreach ($teams as $team) {
            echo '<option value="'.$team['id'].'"';
            if (isset($_POST['team']) && $_POST['team'] == $team['id']) echo ' selected="selected"';
            echo '>',$team['name'],'</option>';
          }
        ?>
    </select><input type="text" name="number" value="0"/><br/><br/>
    HB: <input type="text" name="HB" value=""/><br/>
    HL: <input type="text" name="HL" value=""/><br/>
    GS: <input type="text" name="GS" value=""/><br/>
    LA: <input type="text" name="LA" value=""/><br/>
    FS: <input type="text" name="FS" value=""/><br/>
  <button type="submit">Speichern</button>
  </form>

</div>

