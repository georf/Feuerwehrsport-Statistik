<?php
if (!Check::post('key', 'scoreId')) throw new Exception('no score id given');

$table = false;
$wks = false;
if ($_POST['key'] === 'gs') {
    $table = 'scores_gruppenstafette';
    $wks = 7;
} elseif ($_POST['key'] === 'fs') {
    $table = 'scores_stafette';
    $wks = 5;
} elseif ($_POST['key'] === 'la') {
    $table = 'scores_loeschangriff';
    $wks = 8;
}

if ($table === false || $wks === false) throw new Exception();


if (!Check::isIn($_POST['scoreId'], $table))  throw new Exception('score id not found');

$score = FSS::tableRow($table, $_POST['scoreId']);
$update = false;

for ($i = 1; $i < $wks; $i++) {
    if (Check::post('person_'.$i)) {
        if (Check::isIn($_POST['person_'.$i], 'persons')
            && $score['person_'.$i] != $_POST['person_'.$i]) {

            $db->updateRow($table, $_POST['scoreId'], array(
                'person_'.$i => $_POST['person_'.$i]
            ));
            $update = true;
        } elseif ($_POST['person_'.$i] == 'NULL'
            && $score['person_'.$i] != null) {

            $db->updateRow($table, $_POST['scoreId'], array(
                'person_'.$i => null
            ));
            $update = true;
        }
    }
}


if ($update) {
    $score = FSS::tableRow($table, $_POST['scoreId']);
    Log::insert('set-score-wk', array(
        'key' => $_POST['key'],
        'score' => $score,
        'competition' => FSS::competition($score['competition_id'])
    ));
}

$output['success'] = true;
