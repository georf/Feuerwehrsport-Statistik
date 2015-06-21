<?php
$output['team-spellings'] = $db->getRows("SELECT * FROM `teams_spelling` WHERE team_id IN (SELECT id FROM teams)");
$output['success'] = true;
