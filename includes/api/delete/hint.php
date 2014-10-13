<?php

Check2::except()->isAdmin();
$competitionHintId = Check2::except()->post('competitionHintId')->isIn('competition_hints');

$hint = FSS::tableRow('competition_hints', $competitionHintId);
$result = $db->deleteRow('competition_hints', $competitionHintId);
Log::insert('delete-hint', $hint);

$output['success'] = true;
