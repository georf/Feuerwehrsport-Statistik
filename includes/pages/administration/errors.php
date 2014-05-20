<h1>Logs</h1>

<table class="table" style="width:99%;">
<?php

if (isset($_GET['id']) && Check::isIn($_GET['id'], 'errors')) {
  $error = $db->getFirstRow("
    SELECT *
    FROM `errors`
    WHERE `id` = '".$db->escape($_GET['id'])."'
    LIMIT 1;");
  $post = unserialize($error['content']);

  if ($post['reason'] == 'correction') {
    if ($post['type'] == 'person') {
      $db->updateRow("persons", $post['personId'], array(
        'firstname' => $post['firstname'],
        'name' => $post['name'],
      ));
    } elseif ($post['type'] == 'team') {
      $db->updateRow("teams", $post['teamId'], array(
        'name' => $post['name'],
        'short' => $post['short'],
        'type' => $post['teamType'],
      ));
    }
  } elseif ($post['reason'] == 'together') {
    if ($post['type'] == 'person') {
      $new_person = $db->getFirstRow("
        SELECT *
        FROM `persons`
        WHERE `id` = '".$db->escape($post['newPersonId'])."'
        LIMIT 1;");

      $person = $db->getFirstRow("
        SELECT *
        FROM `persons`
        WHERE `id` = '".$db->escape($post['personId'])."'
        LIMIT 1;");
    
      if (Check::get('always')) {
        $db->insertRow('persons_spelling', array(
          'name' => $person['name'],
          'firstname' => $person['firstname'],
          'sex' => $person['sex'],
          'person_id' => $new_person['id'],
        ));
      }
    
      // set scores
      $scores = $db->getRows("
        SELECT `id`
        FROM `scores`
        WHERE `person_id` = '".$person['id']."'
      ");
      foreach ($scores as $score) {
        $db->updateRow('scores', $score['id'], array('person_id' => $new_person['id']));
      }

      foreach (array('fs', 'gs', 'la') as $key) {
        // set scores
        $scores = $db->getRows("
          SELECT `id`
          FROM `person_participations_".$key."`
          WHERE `person_id` = '".$person['id']."'
        ");
        foreach ($scores as $score) {
          $db->updateRow('person_participations_'.$key, $score['id'], array('person_id' => $new_person['id']));
        }
      }
      
      // set spelling
      $spellings = $db->getRows("
        SELECT `id`
        FROM `persons_spelling`
        WHERE `person_id` = '".$person['id']."'
      ");
      foreach ($spellings as $spell) {
        $db->updateRow('persons_spelling', $spell['id'], array('person_id' => $new_person['id']));
      }
   
      // delete person
      $db->deleteRow('persons', $person['id']);

    } elseif ($post['type'] == 'team')  {

      $new_team = $db->getFirstRow("
        SELECT *
        FROM `teams`
        WHERE `id` = '".$db->escape($post['newTeamId'])."'
        LIMIT 1
      ");
      $team = $db->getFirstRow("
        SELECT *
        FROM `teams`
        WHERE `id` = '".$db->escape($post['teamId'])."'
        LIMIT 1
      ");

      if (Check::get('always')) {
        $db->insertRow('teams_spelling', array(
          'name' => $team['name'],
          'short' => $team['short'],
          'team_id' => $new_team['id'],
        ));
      }
      
      // set scores
      $scores = $db->getRows("
        SELECT `id`
        FROM `scores`
        WHERE `team_id` = '".$team['id']."'
      ");
      foreach ($scores as $score) {
        $db->updateRow('scores', $score['id'], array('team_id' => $new_team['id']));
      }
    
      // set scores
      $scores = $db->getRows("
        SELECT `id`
        FROM `scores_gs`
        WHERE `team_id` = '".$team['id']."'
      ");
      foreach ($scores as $score) {
        $db->updateRow('scores_gs', $score['id'], array('team_id' => $new_team['id']));
      }

      // set scores
      $scores = $db->getRows("
        SELECT `id`
        FROM `scores_la`
        WHERE `team_id` = '".$team['id']."'
      ");
      foreach ($scores as $score) {
        $db->updateRow('scores_la', $score['id'], array('team_id' => $new_team['id']));
      }

      // set scores
      $scores = $db->getRows("
        SELECT `id`
        FROM `scores_fs`
        WHERE `team_id` = '".$team['id']."'
      ");
      foreach ($scores as $score) {
        $db->updateRow('scores_fs', $score['id'], array('team_id' => $new_team['id']));
      }

      // set links
      $links = $db->getRows("
        SELECT `id`
        FROM `links`
        WHERE `for` = 'team'
        AND `for_id` = '".$team['id']."'
      ");
      foreach ($links as $link) {
        $db->updateRow('links', $link['id'], array('for_id' => $new_team['id']));
      }
    
      // set spelling
      $spellings = $db->getRows("
        SELECT `id`
        FROM `teams_spelling`
        WHERE `team_id` = '".$team['id']."'
      ");
      foreach ($spellings as $spell) {
        $db->updateRow('teams_spelling', $spell['id'], array('team_id' => $new_team['id']));
      }
    
      // delete team
      $db->deleteRow('teams', $team['id']);
    }
  } elseif ($post['reason'] == 'change') {
    if ($post['type'] == 'date') {

      $provided = array();
      foreach (FSS::$disciplines as $dis) {
        if (isset($post['date'][$dis]) && $post['date'][$dis] == 'true') {
          $provided[] = strtoupper($dis);
        }
      }
      sort($provided);

      $resultId = $db->updateRow('dates', $post['dateId'], array(
        'date'        => $post['date']['date'],
        'name'        => $post['date']['name'],
        'place_id'    => $post['date']['placeId'],
        'event_id'    => $post['date']['eventId'],
        'description' => $post['date']['description'],
        'disciplines' => implode(',', $provided)
      ));
    }
  } elseif ($post['reason'] == 'logo') {
    if ($post['type'] == 'team') {

      $team = FSS::tableRow("teams", $post['teamId']);

      TeamLogo::build($team)->remove();

      $name = $post['attached_files'][0];
      $basename = preg_replace('|\.[^.]+$|', '', $name);
      $newName = $basename.'.png';

      if ($name != $newName) {
        shell_exec('convert '.$config['error-file-path'].$name.' '.$config['error-file-path'].$newName);
        unlink($config['error-file-path'].$name);
      }

      $n = 1;
      while (is_file($config['logo-path'].$n.$newName)) $n++;

      $db->updateRow('teams', $post['teamId'], array(
        'logo' => $n.$newName
      ));

      if ($name != $newName) {
        shell_exec('convert '.$config['error-file-path'].$name.' '.$config['logo-path'].$n.$newName);
      } else {
        rename($config['error-file-path'].$name, $config['logo-path'].$n.$newName);
      }
      shell_exec('mogrify -resize 100x100 -background transparent -gravity center -extent 100x100 -format png '.$config['logo-path'].$n.$newName);

      Log::insert('add-logo', array('team_id' => $post['teamId']));
    }
  }
  header('Location: ?page=administration&admin=errors');
  exit();
}

if (isset($_GET['delete']) && Check::isIn($_GET['delete'], 'errors')) {
  $error = FSS::tableRow("logs", $_GET["delete"]);
  $post = unserialize($error['content']);
  if (isset($post['attached_files'])) {
    foreach ($post['attached_files'] as $file) {
      if (is_file($config['error-file-path'].$file)) {
        unlink($config['error-file-path'].$file);
      }
    }
  }
  $db->deleteRow('errors', $_GET['delete'], 'id', false);
  header('Location: ?page=administration&admin=errors');
  exit();
}

$errors = $db->getRows("
  SELECT *
  FROM `errors`
  ORDER BY `inserted` DESC;
");

foreach($errors as $error) {
  echo '<tr style="border-top:22px solid #E5E5E5;">';
  echo '<th>'.date('d.m.Y H:i', strtotime($error['inserted'])).'</th>';
  echo '<th>'.Login::getNameLink($error['user_id']).' '.Login::getMailLink($error['user_id']).'</th>';
  echo '<th></th>';
  echo '</tr>';
  echo '<tr>';

  $post = unserialize($error['content']);

  if ($post['type'] == 'person') {
    if (!isset($post['reason'])) {
      continue;
    }

    switch ($post['reason']) {
      case 'correction':
        $ok = true;

        echo '<td>Vorname: '.$post['firstname'].'<br/>Name: '.$post['name'].'</td>';
        if (!person_to_td($post['personId'])) $ok = false;

        if ($ok) {
          echo '<td><a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'">OK</a> ';
          echo '</td>';
        } else {
          echo '<td></td>';
        }
        break;

      case 'together':
        $ok = true;

        if (!person_to_td($post['newPersonId'], 'Richtig: ')) $ok = false;
        if (!person_to_td($post['personId'])) $ok = false;

        if ($ok) {
          echo '<td><a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'">OK</a> ';
          echo '<a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'&amp;always=1">Immer</a></td>';
        } else {
          echo '<td></td>';
        }
        break;

      case 'other':
        person_to_td($post['personId']);
        echo '<td colspan="2">'.nl2br($post['description']).'</td>';
        break;

      default:
        echo '<td colspan="3">'.$error['content'].'</td>';
        break;
    }
  } elseif ($post['type'] == 'team') {
    switch ($post['reason']) {
      case 'wrong':
      case 'correction':
        $ok = true;
        echo '<td>Name: '.$post['name'].'<br/>Kurz: '.$post['short'].'<br/>Typ: '.$post['teamType'].'</td>';
        if (!team_to_td($post['teamId'])) $ok = false;

        if ($ok) {
          echo '<td><a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'">OK</a> ';
          echo '</td>';
        } else {
          echo '<td></td>';
        }
        break;
      case 'together':
        $ok = true;

        if (!team_to_td($post['newTeamId'], 'Richtig: ')) $ok = false;
        if (!team_to_td($post['teamId'])) $ok = false;

        if ($ok) {
          echo '<td><a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'">OK</a> ';
          echo '<a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'&amp;always=1">Immer</a></td>';
        } else {
          echo '<td></td>';
        }
        break;

      case 'other':
        team_to_td($post['teamId']);
        echo '<td colspan="2">'.nl2br($post['description']).'</td>';
        break;

      case 'logo':
        team_to_td($post['teamId']);
        echo '<td>';
        foreach ($post['attached_files'] as $file) {
          echo '<img src="/files/errors/'.$file.'" width=100/>';
        }
        echo '<td><a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'">OK</a></td>';
        echo '</td>';
        break;      

      default:
        echo '<td colspan="3">'.$error['content'].'</td>';
        break;
    }
  } elseif ($post['type'] == 'date') {
    $date = $post['date'];


    switch ($post['reason']) {
      case 'change':
        $provided = array();
        foreach (FSS::$disciplines as $dis) {
          if (isset($date[$dis]) && $date[$dis] == 'true') {
            $provided[] = strtoupper($dis);
          }
        }
        sort($provided);
        echo '<td>'.htmlspecialchars($date['name']).'<br/>'.$date['date'].'<br/>'.implode(', ', $provided).'</td>';
        echo '<td>'.nl2br(htmlspecialchars($date['description'])).'</td>';
        echo '<td>';
        if (!empty($date['placeId'])) echo Link::place($date['placeId']).'<br/>';
        if (!empty($date['eventId'])) echo Link::event($date['eventId']).'<br/>';
        echo '<a class="btn" href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'">OK</a> ';
        echo '</td>';
        break;

      default:
        echo '<td colspan="3">'.$error['content'].'</td>';
        break;
    }
  } else {
    echo '<td colspan="3">'.$error['content'].'</td>';
  }
  echo '</tr><tr><td colspan="2"></td><td><a onclick="return confirm(\'Wirklich löschen?\');" href="?page=administration&amp;admin=errors&amp;delete='.$error['id'].'">Löschen</a></td></tr>';
}

echo '</table>';

function person_to_td($id, $a = '') {
  global $db;

  if (Check::isIn($id, 'persons')) {
    $person = $db->getFirstRow("
      SELECT *
      FROM `persons`
      WHERE `id` = '".$db->escape($id)."'
      LIMIT 1;");

    echo '<td>'.$a.$person['name'].', '.$person['firstname'].' ('.$person['sex'].') '.Link::person($id, '#'),'</td>';
    return true;
  } else {
    echo '<td>Personen wurden geändert</td>';
    return false;
  }
}

function team_to_td($id, $a = '') {
  global $db;

  if (Check::isIn($id, 'teams')) {
    $team = $db->getFirstRow("
      SELECT *
      FROM `teams`
      WHERE `id` = '".$db->escape($id)."'
      LIMIT 1;");

    echo '<td>'.$a.$team['name'].'  ('.$team['type'].') '.Link::team($id, '#'),'</td>';
    return true;
  } else {
    echo '<td>Team wurde geändert</td>';
    return false;
  }
}
