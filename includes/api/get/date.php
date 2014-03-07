<?php

$output['date'] = Check2::except()->post('dateId')->isIn('dates', 'row');
$output['success'] = true;
