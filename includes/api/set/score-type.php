<?php
if (!Check::post('id', 'score_type_id')
|| !Check::isIn($_POST['id'], 'competitions')
|| $_POST['score_type_id'] != 0  && !Check::isIn($_POST['score_type_id'], 'score_types')) throw new Exception("bad input");

$db->updateRow('competitions', $_POST['id'], array(
    'score_type_id' => $_POST['score_type_id']
));

Log::insert('set-score-type', array(
    'competition' => FSS::tableRow('competitions', $_POST['id'])
));
