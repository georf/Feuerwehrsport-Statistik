<?php
if (!Check::post('personId') || !Check::isIn($_POST['personId'], 'persons')) throw new Exception();

$output = $db->getFirstRow("
    SELECT *
    FROM `persons`
    WHERE `id` = '".$_POST['personId']."'
    LIMIT 1");
$output['success'] = true;
