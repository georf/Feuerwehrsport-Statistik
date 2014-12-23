<?php
$competition = Check2::except()->post('competitionId')->isIn('competitions', 'row');

$output['published'] = $competition['published'];
$output['success'] = true;
