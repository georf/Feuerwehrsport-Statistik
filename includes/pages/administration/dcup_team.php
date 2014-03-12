<?php

if (Check::post('competition', 'team', 'number','sex')) {
  foreach (array('HL','HB', 'GS','FS','LA') as $d) {
    if (Check::post($d.'-points', $d.'-time')) {
      $time = trim($_POST[$d.'-time']);
      if ($time == 'D') $time = 'NULL';

      $db->insertRow('scores_dcup_team', array(
        'competition_id' => $_POST['competition'],
        'team_id' => $_POST['team'],
        'dcup_id' => $_POST['dcup'],
        'team_number' => $_POST['number'],
        'sex' => $_POST['sex'],
        'points' => $_POST[$d.'-points'],
        'time' => $time,
        'discipline' => $d,
      ), false);
    }
  }
}

TempDB::generate('x_full_competitions');

$dcups = $db->getRows("
  SELECT *
  FROM `dcups`
  ORDER BY `year` DESC
");

$competitions = $db->getRows("
  SELECT *
  FROM `x_full_competitions`
  WHERE `event_id` = 1
  ORDER BY `date` DESC
");

$teams = $db->getRows("
  SELECT *
  FROM `teams`
  ORDER BY `name`
");
?>
<form method="post" action="">
  <h3>Wettkampf</h3>
  <select name="sex">
    <?php
      foreach (array('female', 'male') as $sex) {
        echo '<option value="'.$sex.'"';
        if (isset($_POST['sex']) && $_POST['sex'] == $sex) echo ' selected="selected"';
          echo '>',FSS::sex($sex).'</option>';
        }
    ?>
  </select>
  <select name="dcup">
    <?php
      foreach ($dcups as $dcup) {
        echo '<option value="'.$dcup['id'].'"';
        if (isset($_POST['dcup']) && $_POST['dcup'] == $dcup['id']) echo ' selected="selected"';
          echo '>',$dcup['year'].'</option>';
        }
    ?>
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
  </select>
  <select name="number">
    <option value="0">1</option>
    <option value="1">2</option>
    <option value="2">3</option>
  </select>
  <br/>
<table>
<tr><th></th><th>Zeit</th><th>Punkte</th></tr>
<tr id="hb"><th>HB:</th><td><input type="text" name="HB-time" value=""/><td><input type="text" name="HB-points" value=""/></td></td></tr>
<tr id="hl"><th>HL:</th><td><input type="text" name="HL-time" value=""/><td><input type="text" name="HL-points" value=""/></td></td></tr>
<tr id="gs"><th>GS:</th><td><input type="text" name="GS-time" value=""/><td><input type="text" name="GS-points" value=""/></td></td></tr>
<tr id="la"><th>LA:</th><td><input type="text" name="LA-time" value=""/><td><input type="text" name="LA-points" value=""/></td></td></tr>
<tr id="fs"><th>FS:</th><td><input type="text" name="FS-time" value=""/><td><input type="text" name="FS-points" value=""/></td></td></tr>
</table>
  <button type="submit">Speichern</button>
  </form>

</div>

