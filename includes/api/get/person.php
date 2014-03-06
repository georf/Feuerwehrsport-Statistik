<?php

$output['person'] = Check2::except()->post('personId')->isIn('persons', 'row');
$output['success'] = true;
