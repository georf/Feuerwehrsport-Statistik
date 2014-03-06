<?php

$output['team'] = Check2::except()->post('teamId')->isIn('teams', 'row');
$output['success'] = true;
