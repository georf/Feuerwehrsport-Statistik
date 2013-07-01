<?php


if (Check::post('id', 'status') && Check::isIn($_POST['id'], 'competitions') && in_array($_POST['status'], array('0','1','2','3','4','5'))) {
    $db->updateRow('competitions', $_POST['id'], array('status'=>$_POST['status']));
}

if (isset($_POST['type'])) {
  if ($_POST['type'] == 'event' && isset($_POST['name'])) {
    $db->insertRow('events', array('name'=>$_POST['name']));

    header('Location: ?page='.$_GET['page'].'&admin='.$_GET['admin']);
    exit();
  }
  if ($_POST['type'] == 'place' && isset($_POST['name'])) {
    $db->insertRow('places', array('name'=>$_POST['name']));

    header('Location: ?page='.$_GET['page'].'&admin='.$_GET['admin']);
    exit();
  }
  if ($_POST['type'] == 'competition'
  && isset($_POST['event'],$_POST['place'],$_POST['date'])
  && is_numeric($_POST['event'])
  && is_numeric($_POST['place'])
  && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_POST['date'])) {

    $name = (isset($_POST['name']) && !empty($_POST['name']))? $_POST['name'] : NULL;
    $db->insertRow('competitions', array(
      'name'=>$name,
      'place_id' => $_POST['place'],
      'event_id' => $_POST['event'],
      'date' => $_POST['date']
    ));

    header('Location: ?page='.$_GET['page'].'&admin='.$_GET['admin']);
    exit();
  }
}
?>


<p>
    <span id="top-competitions" style="border:1px solid blue;margin:3px;padding:3px;">Wettkämpfe</span>
    <span id="top-events" style="border:1px solid blue;margin:3px;padding:3px;">Events</span>
    <span id="top-places" style="border:1px solid blue;margin:3px;padding:3px;">Places</span>
</p>

<div class="tab container" id="tab-competitions">
<button class="C_add">Hinzufügen</button>
  <table class="table">
    <tr><th>Typ</th><th>Ort</th><th>Name</th><th>Datum</th></tr>

<?php
$events = $db->getRows("
  SELECT *
  FROM `events`
  ORDER BY `name`
");

$places = $db->getRows("
  SELECT *
  FROM `places`
  ORDER BY `name`
");

$competitions = $db->getRows("
  SELECT `c`.*,`e`.`name` AS `event`, `p`.`name` AS `place`
  FROM `competitions` `c`
  INNER JOIN `events` `e` ON `c`.`event_id` = `e`.`id`
  INNER JOIN `places` `p` ON `c`.`place_id` = `p`.`id`
  ORDER BY `c`.`date` DESC;
");

foreach ($competitions as $competition) {
  echo '<tr>
    <td>',$competition['event'],'</td>
    <td>',$competition['place'],'</td>
    <td>',$competition['name'],'</td>
    <td>',$competition['date'],'</td>
  </tr>';
}

?>
</table>
<button class="C_add">Hinzufügen</button>
</div>

<div class="formbox" id="C_add_box">
  <h3>Wettkampf</h3>
  <form method="post" action="">
    <input type="hidden" value="competition" name="type"/>

    <label for="C_event">Veranstaltung:</label>
    <select id="C_event" name="event">
      <?php
        foreach ($events as $event) {
          echo '<option value="'.$event['id'].'">'.$event['name'].'</option>';
        }
      ?>
    </select>

    <br/>

    <label for="C_place">Ort:</label>
    <select id="C_place" name="place">
      <?php
        foreach ($places as $place) {
          echo '<option value="'.$place['id'].'">'.$place['name'].'</option>';
        }
      ?>
    </select>

    <br/>

    <label for="C_date">Datum (YYYY-MM-DD):</label>
    <input id="C_date" type="text" name="date"/>

    <br/>

    <label for="C_name">Name (optional):</label>
    <input id="C_name" type="text" name="name"/>

    <br/>

    <button type="submit" id="C_submit">Eintragen</button><button id="C_cancel" class="cancel-button">Abbrechen</button>
  </form>
</div>


<div class="tab container" id="tab-events">
<button class="E_add">Hinzufügen</button>
<table class="table">
<?php

foreach ($events as $event) {
  echo '<tr><td>',$event['name'],'</td></tr>';
}

?>
</table>
<button class="E_add">Hinzufügen</button>
</div>

<div class="formbox" id="E_add_box">
  <h3>Veranstaltung</h3>
  <form method="post" action="">
    <input type="hidden" value="event" name="type"/>

    <label for="E_name">Name:</label>
    <input id="E_name" type="text" name="name"/>

    <br/>

    <button type="submit" id="E_submit">Eintragen</button><button id="E_cancel" class="cancel-button">Abbrechen</button>
  </form>
</div>

<div class="tab container" id="tab-places">
<button class="P_add">Hinzufügen</button>
<table class="table">
<?php

foreach ($places as $place) {
  echo '<tr><td>',$place['name'],'</td></tr>';
}

?>
</table>
<button class="P_add">Hinzufügen</button>
</div>

<div class="formbox" id="P_add_box">
  <h3>Ort</h3>
  <form method="post" action="">
    <input type="hidden" value="place" name="type"/>

    <label for="P_name">Name:</label>
    <input id="P_name" type="text" name="name"/>

    <br/>

    <button type="submit" id="P_submit">Eintragen</button><button id="P_cancel" class="cancel-button">Abbrechen</button>
  </form>
</div>



<script type="text/javascript">
$(function(){

  var darkroom = $('<div class="darkroom"></div>').appendTo('body');

  $('.C_add').click(function(){
    darkroom.show();
    $('#C_add_box').show();
    return false;
  });

  $('.E_add').click(function(){
    darkroom.show();
    $('#E_add_box').show();
    return false;
  });
  $('.P_add').click(function(){
    darkroom.show();
    $('#P_add_box').show();
    return false;
  });

  $('.cancel-button').click(function() {
    darkroom.hide();
    $('.formbox').hide();
    return false;
  });

  $('.status-select').change(function() {
      $(this).closest('form').submit();
    });
    
    
    $('#top-competitions, #top-events, #top-places').click(function() {
        $('.tab').hide();
        $('#' + $(this).attr('id').replace(/top-/, 'tab-')).show();
    });
    $('#top-competitions').click();

});
</script>
