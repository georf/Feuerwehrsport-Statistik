<?php

$output['competition'] = Check2::except()->post('competitionId')->isIn('competitions', 'row');
$output['success'] = true;
