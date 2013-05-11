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

    if ($post['type'] == 'person') {
        $new_person = $db->getFirstRow("
            SELECT *
            FROM `persons`
            WHERE `id` = '".$db->escape($post['new_person_id'])."'
            LIMIT 1;");
        $person = $db->getFirstRow("
            SELECT *
            FROM `persons`
            WHERE `id` = '".$db->escape($post['person_id'])."'
            LIMIT 1;");

        // set scores
        $scores = $db->getRows("
            SELECT `id`
            FROM `scores`
            WHERE `person_id` = '".$person['id']."'");
        foreach ($scores as $score) {
            $db->updateRow('scores', $score['id'], array('person_id' => $new_person['id']));
        }

        for($i = 1; $i < 7; $i++) {

            // set scores
            $scores = $db->getRows("
                SELECT `id`
                FROM `scores_gs`
                WHERE `person_".$i."` = '".$person['id']."'");
            foreach ($scores as $score) {
                $db->updateRow('scores_gs', $score['id'], array('person_'.$i => $new_person['id']));
            }
        }

        for($i = 1; $i < 8; $i++) {

            // set scores
            $scores = $db->getRows("
                SELECT `id`
                FROM `scores_la`
                WHERE `person_".$i."` = '".$person['id']."'");
            foreach ($scores as $score) {
                $db->updateRow('scores_la', $score['id'], array('person_'.$i => $new_person['id']));
            }
        }

        for($i = 1; $i < 5; $i++) {

            // set scores
            $scores = $db->getRows("
                SELECT `id`
                FROM `scores_fs`
                WHERE `person_".$i."` = '".$person['id']."'");
            foreach ($scores as $score) {
                $db->updateRow('scores_fs', $score['id'], array('person_'.$i => $new_person['id']));
            }
        }

        // set scores
        $memberships = $db->getRows("
            SELECT `id`
            FROM `team_memberships`
            WHERE `person_id` = '".$person['id']."'");
        foreach ($scores as $score) {
            $db->updateRow('team_memberships', $score['id'], array('person_id' => $new_person['id']));
        }

        // delete person
        $db->deleteRow('persons', $person['id']);

    } elseif ($post['type'] == 'team')  {
        $new_team = $db->getFirstRow("
            SELECT *
            FROM `teams`
            WHERE `id` = '".$db->escape($post['new_team_id'])."'
            LIMIT 1;");
        $team = $db->getFirstRow("
            SELECT *
            FROM `teams`
            WHERE `id` = '".$db->escape($post['team_id'])."'
            LIMIT 1;");

        // set scores
        $scores = $db->getRows("
            SELECT `id`
            FROM `scores`
            WHERE `team_id` = '".$team['id']."'");
        foreach ($scores as $score) {
            $db->updateRow('scores', $score['id'], array('team_id' => $new_team['id']));
        }

        // set scores
        $scores = $db->getRows("
            SELECT `id`
            FROM `scores_gs`
            WHERE `team_id` = '".$team['id']."'");
        foreach ($scores as $score) {
            $db->updateRow('scores_gs', $score['id'], array('team_id' => $new_team['id']));
        }

        // set scores
        $scores = $db->getRows("
            SELECT `id`
            FROM `scores_la`
            WHERE `team_id` = '".$team['id']."'");
        foreach ($scores as $score) {
            $db->updateRow('scores_la', $score['id'], array('team_id' => $new_team['id']));
        }

        // set scores
        $scores = $db->getRows("
            SELECT `id`
            FROM `scores_fs`
            WHERE `team_id` = '".$team['id']."'");
        foreach ($scores as $score) {
            $db->updateRow('scores_fs', $score['id'], array('team_id' => $new_team['id']));
        }

        // set scores
        $memberships = $db->getRows("
            SELECT `id`
            FROM `team_memberships`
            WHERE `team_id` = '".$team['id']."'");
        foreach ($scores as $score) {
            $db->updateRow('team_memberships', $score['id'], array('team_id' => $new_team['id']));
        }

	// set links
	$links = $db->getRows("
            SELECT `id`
            FROM `links`
            WHERE `for` = 'team'
            AND `for_id` = '".$team['id']."'");
        foreach ($links as $link) {
            $db->updateRow('links', $link['id'], array('for_id' => $new_team['id']));
        }


        // delete team
        $db->deleteRow('teams', $team['id']);
    }

}
if (isset($_GET['delete']) && Check::isIn($_GET['delete'], 'errors')) {
    $db->deleteRow('errors', $_GET['delete']);
}
/*
$lines = $db->getRows("SELECT `k`.`id` AS `person_id` , `g`.`id` AS `new_person_id`
FROM (

SELECT *
FROM `persons`
WHERE `name` LIKE '%,'
) `k` , `persons` `g`
WHERE `g`.`name` = REPLACE( `k`.`name` , ',', '' )
AND `g`.`firstname` = `k`.`firstname`");

$lines = $db->getRows("SELECT `k`.`id` AS `person_id` , `g`.`id` AS `new_person_id`
FROM (

SELECT *
FROM `persons`
WHERE `sex` LIKE 'male'
) `k` , `persons` `g`
WHERE `g`.`name` = `k`.`name`
AND `g`.`firstname` = `k`.`firstname`
AND `g`.`sex` = 'female'");

foreach ($lines as $line) {
    $line['reason'] = 'together';
    $line['type'] = 'person';

    $db->insertRow('errors', array(
                    'user_id' => Login::getId(),
                    'content' => serialize($line)
                ));
}*/

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


            case 'together':
                $ok = true;

                if (!person_to_td($post['new_person_id'], 'Richtig: ')) $ok = false;
                if (!person_to_td($post['person_id'])) $ok = false;

                if ($ok) {
                    echo '<td><a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'">OK</a></td>';

                } else {
                    echo '<td></td>';
                }
            break;



            case 'other':
                person_to_td($post['person_id']);
                echo '<td colspan="2">'.nl2br($post['description']).'</td>';
            break;

            default:
                echo '<td colspan="3">'.$error['content'].'</td>';
            break;
        }
    } elseif ($post['type'] == 'team') {

        switch ($post['reason']) {


            case 'together':
                $ok = true;

                if (!team_to_td($post['new_team_id'], 'Richtig: ')) $ok = false;
                if (!team_to_td($post['team_id'])) $ok = false;

                if ($ok) {
                    echo '<td><a href="?page=administration&amp;admin=errors&amp;id='.$error['id'].'">OK</a></td>';

                } else {
                    echo '<td></td>';
                }
            break;



            case 'other':
                team_to_td($post['team_id']);
                echo '<td colspan="2">'.nl2br($post['description']).'</td>';
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



?>
</table>



<?php


function person_to_td($id, $a = '') {
    global $db;

    if (Check::isIn($id, 'persons')) {
        $person = $db->getFirstRow("
            SELECT *
            FROM `persons`
            WHERE `id` = '".$db->escape($id)."'
            LIMIT 1;");

        echo '<td>'.$a.$person['name'].', '.$person['firstname'].' ('.$person['sex'].') <a href="?page=person&amp;id='.$id.'">#</a></td>';
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

        echo '<td>'.$a.$team['name'].'  ('.$team['type'].') <a href="?page=team&amp;id='.$id.'">#</a></td>';
        return true;
    } else {
        echo '<td>Team wurde geändert</td>';
        return false;
    }
}
