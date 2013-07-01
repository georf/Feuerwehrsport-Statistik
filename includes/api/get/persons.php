<?php

$where = '';
if (Check::post('sex') && in_array($_POST['sex'], array('male','female'))) {
    $where = " WHERE `sex`  = '".$_POST['sex']."' ";
}

$output['persons'] = $db->getRows("
    SELECT `name`,`firstname`,`id`,`sex`
    FROM `persons`
    ".$where."
    ORDER BY `name`, `firstname`
");
